<?php

namespace Drupal\webform_civicrm;

/**
 * @file
 * Front-end form validation and post-processing.
 */

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform_civicrm\WebformCivicrmBase;


class WebformCivicrmPostProcess extends WebformCivicrmBase implements WebformCivicrmPostProcessInterface {
  // Variables used during validation

  /**
   * @var array
   */
  private $form;

  /**
   * @var \Drupal\Core\Form\FormStateInterface
   */
  private $form_state;
  private $crmValues;
  private $rawValues;
  private $multiPageDataLoaded;
  private $billing_params = [];
  private $totalContribution = 0;
  private $contributionIsIncomplete = FALSE;
  private $contributionIsPayLater = FALSE;

  // During validation this contains an array of known contact ids and the placeholder 0 for valid contacts
  // During submission processing this contains an array of known contact ids
  private $existing_contacts = [];

  // Variables used during submission processing

  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * @var \Drupal\webform\WebformSubmissionInterface
   */
  private $submission;
  private $all_fields;
  private $all_sets;
  private $shared_address = [];

  /**
   * Static cache.
   *
   * @var bool
   */
  protected $initialized = FALSE;

  const
    BILLING_MODE_LIVE = 1,
    BILLING_MODE_MIXED = 3;

  public function __construct(UtilsInterface $utils) {
    $this->utils = $utils;
  }

  function initialize(WebformSubmissionInterface $webform_submission) {
    if ($this->initialized) {
      return $this;
    }

    $this->node = $webform_submission->getWebform();

    $handler_collection = $this->node->getHandlers('webform_civicrm');
    $instance_ids = $handler_collection->getInstanceIds();
    $handler = $handler_collection->get(reset($instance_ids));
    $this->database = \Drupal::database();

    $this->settings = $handler->getConfiguration()['settings'];
    $this->data = $this->settings['data'];
    $this->enabled = $this->utils->wf_crm_enabled_fields($this->node);
    $this->all_fields = $this->utils->wf_crm_get_fields();
    $this->all_sets = $this->utils->wf_crm_get_fields('sets');

    $this->initialized = TRUE;
    return $this;
  }

  /**
   * Called after a webform is submitted
   * Or, for a multipage form, called after each page
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function validate($form, FormStateInterface $form_state) {
    $this->form = $form;
    $this->form_state = $form_state;
    $this->rawValues = $form_state->getValues();
    $this->crmValues = $this->utils->wf_crm_enabled_fields($this->node, $this->rawValues);
    // Even though this object is destroyed between page submissions, this trick allows us to persist some data - see below
    $this->ent = $form_state->get(['civicrm', 'ent']) ?: $this->ent;

    $errors = $this->form_state->getErrors();
    foreach ($errors as $key => $error) {
      $pieces = $this->utils->wf_crm_explode_key(substr($key, strrpos($key, '][') + 2));
      if ($pieces) {
        [ , $c, $ent, $n, $table, $name] = $pieces;
        if ($this->isFieldHiddenByExistingContactSettings($ent, $c, $table, $n, $name)) {
          $this->unsetError($key);
        }
        elseif ($table === 'address' && !empty($this->crmValues["civicrm_{$c}_contact_{$n}_address_master_id"])) {
          $master_id = $this->crmValues["civicrm_{$c}_contact_{$n}_address_master_id"];
          // If widget is checkboxes, need to filter the array
          if (!is_array($master_id) || array_filter($master_id)) {
            $this->unsetError($key);
          }
        }
      }
    }

    $this->validateThisPage($this->form);

    if (!empty($this->data['participant']) && !empty($this->data['participant_reg_type'])) {
      $this->loadMultiPageData();
      $this->validateParticipants();
    }

    // Process live contribution. If the transaction is unsuccessful it will trigger a form validation error.
    $contribution_enabled = wf_crm_aval($this->data, 'contribution:1:contribution:1:enable_contribution');
    if ($contribution_enabled) {
      // Ensure contribution js is still loaded if the form has to refresh
      $this->addPaymentJs();
      $this->loadMultiPageData();
      if ($this->tallyLineItems()) {
        if ($this->isLivePaymentProcessor() && $this->isPaymentPage() && !$form_state->getErrors()) {
          if ($this->validateBillingFields()) {
            if ($this->createBillingContact()) {
              $this->submitLivePayment();
            }
          }
        }
      }
    }
    // Even though this object is destroyed between page submissions, this trick allows us to persist some data - see above
    $form_state->set(['civicrm', 'ent'], $this->ent);
    // TODO: Is line_items being set but never retrieved?
    $form_state->set(['civicrm', 'line_items'], $this->line_items);
  }

  /**
   * Modify data in webform submission. civicrm_options element's key is submitted to the delta
   * column of webform_submission_data. Make sure it is not set to a string value.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   */
  protected function modifyWebformSubmissionData(WebformSubmissionInterface $webform_submission) {
    $data = $webform_submission->getData();
    $webform = $webform_submission->getWebform();
    foreach ($data as $field_key => $val) {
      $element = $webform->getElement($field_key);
      if ($element && $element['#type'] == 'civicrm_options' && is_array($val) && count(array_filter(array_keys($val), 'is_string')) > 0) {
        $data[$field_key] = array_values($val);
      }
      if (empty($data[$field_key]) && !empty($_POST[$field_key])) {
         $data[$field_key] = $_POST[$field_key];
      }
    }
    $webform_submission->setData($data);
  }

  /**
   * Process webform submission when it is about to be saved. Called by the following hook:
   *
   * @see webform_civicrm_webform_submission_presave
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   */
  public function preSave(WebformSubmissionInterface $webform_submission) {
    $this->submission = $webform_submission;
    $this->data = $this->settings['data'];
    $this->modifyWebformSubmissionData($webform_submission);

    // Fill $this->id from existing contacts
    $this->getExistingContactIds();

    // While saving a draft, just skip to postSave and write the record
    if ($this->submission->isDraft()) {
      return;
    }

    $this->fillDataFromSubmission();
    //Fill Custom Contact reference fields.
    $this->fillContactRefs(TRUE);

    // Create/update contacts
    foreach ($this->data['contact'] as $c => $contact) {
      if (empty($this->ent['contact'][$c]['id'])) {
        // Don't create contact if we don't have a name or email
        if ($this->isContactEmpty($contact)) {
          $this->ent['contact'][$c]['id'] = 0;
          continue;
        }
        $this->ent['contact'][$c]['id'] = $this->findDuplicateContact($contact);
      }

      // Current employer must wait for ContactRef ids to be filled
      unset($contact['contact'][1]['employer_id']);

      $newContact = empty($this->ent['contact'][$c]['id']);

      // Avoid Issue with lowercase validation of webform
      if (!empty($contact['contact'][1]['image_url'])) {
        $contact['contact'][1]['image_URL'] = $contact['contact'][1]['image_url'];
        unset($contact['contact'][1]['image_url']);
      }

      // Create new contact
      if ($newContact) {
        $this->ent['contact'][$c]['id'] = $this->createContact($contact);
      }
      if ($c == 1) {
        $this->setLoggingContact();
      }
      // Update existing contact
      if (!$newContact) {
        $this->updateContact($contact, $c);
      }
    }
    // $this->ent['contact'] will now contain all contacts in order, with 0 as a placeholder id for any contact not saved
    ksort($this->ent['contact']);

    // Once all contacts are saved we can fill contact ref fields
    $this->fillContactRefs();

    foreach ($this->data['contact'] as $c => $contact) {
      $cid = $this->ent['contact'][$c]['id'];
      if ($cid) {
        $this->saveMultiRecordCustomData($contact, $c, $cid);
      }
    }

    // Save a non-live transaction
    if ($this->totalContribution && empty($this->ent['contribution'][1]['id'])) {
      $this->createDeferredPayment();
    }

    // Create/update other data associated with contacts
    foreach ($this->data['contact'] as $c => $contact) {
      $cid = $this->ent['contact'][$c]['id'];
      if (!$cid) {
        continue;
      }
      if (!empty($contact['update_contact_ref'])) {
        $this->saveContactRefs($contact, $cid);
      }
      $this->saveCurrentEmployer($contact, $cid);

      $this->fillHiddenContactFields($cid, $c);

      $this->saveContactLocation($contact, $cid, $c);

      $this->saveGroupsAndTags($contact, $cid, $c);

      $this->saveRelationships($contact, $cid, $c);

      // Process event participation
      if (isset($this->all_sets['participant']) && !empty($this->data['participant_reg_type'])) {
        $this->processParticipants($c, $cid);
      }
    }
    // We do this after all contacts and addresses exist
    $this->processSharedAddresses();

    // Process memberships after relationships have been created
    foreach ($this->ent['contact'] as $c => $contact) {
      if ($contact['id'] && isset($this->all_sets['membership']) && !empty($this->data['membership'][$c]['number_of_membership'])) {
        $this->processMemberships($c, $contact['id']);
      }
    }
  }

  /**
   * Process webform submission after it is has been saved. Called by the following hooks:
   * @see webform_civicrm_webform_submission_insert
   * @see webform_civicrm_webform_submission_update
   * @param stdClass $submission
   */
  public function postSave(WebformSubmissionInterface $webform_submission) {
    $this->submission = $webform_submission;
    if (empty($this->submission->isDraft())) {
      // Save cases
      if (!empty($this->data['case']['number_of_case'])) {
        $this->processCases();
      }
      // Save activities
      if (!empty($this->data['activity']['number_of_activity'])) {
        $this->processActivities();
      }
      // Save grants
      if (isset($this->data['grant']['number_of_grant'])) {
        $this->processGrants();
      }
      // Save contribution line-items
      if (!empty($this->ent['contribution'][1]['id'])) {
        $this->processContribution();
      }
    }
    // Write record; we do this when creating, updating, or saving a draft of a webform submission.
    $record = $this->formatSubmission();
    $this->database->merge('webform_civicrm_submissions')
      ->key('sid', $record['sid'])
      ->fields($record)
      ->execute();

    // Calling an IPN payment processor will result in a redirect so this happens after everything else
    if ($this->contributionIsIncomplete && !$this->contributionIsPayLater && !empty($this->ent['contribution'][1]['id']) && !$this->submission->isDraft()) {
//      webform_submission_send_mail($this->node, $this->submission);
      $this->submitIPNPayment();
    }
    $isEmailReceipt = wf_crm_aval($this->data, "receipt:number_number_of_receipt", FALSE);
    // Send receipt
    if (empty($this->submission->isDraft())
      && !empty($this->ent['contribution'][1]['id'])
      && !empty($isEmailReceipt)
      && (!$this->contributionIsIncomplete || $this->contributionIsPayLater)
    ) {
      $this->sendReceipt();
    }
  }

  /**
   * Send receipt
   */
  private function sendReceipt() {
    // tax integration
    if (!is_null($this->tax_rate)) {
      $dataArray = [];
      $totalTaxAmount = NULL;
      foreach ($this->line_items as $key => $value) {
        if (isset($value['tax_amount']) && isset($value['tax_rate'])) {
          if (isset($dataArray[$value['tax_rate']])) {
            $dataArray[$value['tax_rate']] = $dataArray[$value['tax_rate']] + $value['tax_amount'];
          }
          else {
            $dataArray[$value['tax_rate']] = $value['tax_amount'];
          }
          $totalTaxAmount += $value['tax_amount'];
        }
      }
      $template = \CRM_Core_Smarty::singleton();
      $template->assign('dataArray', $dataArray);
      $template->assign('totalTaxAmount', $totalTaxAmount);
    }
    if ($this->contributionIsIncomplete) {
      $template = \CRM_Core_Smarty::singleton();
      $template->assign('is_pay_later', 1);
    }
    $this->utils->wf_civicrm_api('contribution', 'sendconfirmation', $this->utils->getReceiptParams($this->data, $this->ent['contribution'][1]['id']));
  }

  /**
   * Formats submission data as expected by the schema
   */
  private function formatSubmission() {
    $data = $this->ent;
    $record = [
      'sid' => $this->submission->id(),
      'contact_id' => '-',
      'civicrm_data' => serialize($data),
    ];
    foreach ($this->ent['contact'] as $contact) {
      $record['contact_id'] .= $contact['id'] . '-';
    }
    return $record;
  }

  /**
   * Recursive validation callback for webform page submission
   *
   * @todo this shouldn't be needed as each element should handle this.
   *
   * @param array $elements
   *   FAPI form array
   */
  private function validateThisPage($elements) {
    // Recurse through form elements.
    foreach (Element::children($elements) as $key) {
      if (is_array($elements[$key]) && ($element = $elements[$key])) {
        $this->validateThisPage($elements[$key]);

        if (empty($element['#civicrm_data_type'])) {
          continue;
        }
        if (!isset($element['#value']) || $element['#value'] === '') {
          continue;
        }
        $element_type = $element['#type'] ?? '';
        if (strpos($element_type, 'text') !== 0) {
          continue;
        }

        $dt = $element['#civicrm_data_type'];
        // Validate state/prov abbreviation
        if ($dt === 'state_province_abbr') {
          $ckey = str_replace('state_province', 'country', $key);
          if (!empty($this->crmValues[$ckey]) && is_numeric($this->crmValues[$ckey])) {
            $country_id = $this->crmValues[$ckey];
          }
          else {
            $country_id = (int) $this->utils->wf_crm_get_civi_setting('defaultContactCountry', 1228);
          }
          $states = $this->utils->wf_crm_get_states($country_id);
          if ($states && !array_key_exists(strtoupper($element['#value']), $states)) {
            $countries = $this->utils->wf_crm_apivalues('address', 'getoptions', ['field' => 'country_id']);
            $this->form_state->setError($element, t('Mismatch: "@state" is not a state/province of %country. Please enter a valid state/province abbreviation for %field.', ['@state' => $element['#value'], '%country' => $countries[$country_id], '%field' => $element['#title']]));
          }
        }
        // Strings and files don't need any validation
        elseif ($dt !== 'String' && $dt !== 'Memo' && $dt !== 'File'
          && \CRM_Utils_Type::escape($element['#value'], $dt, FALSE) === NULL) {
          // Allow data type names to be translated
          switch ($dt) {
            case 'Int':
              $dt = t('an integer');
              break;
            case 'Float':
              $dt = t('a number');
              break;
            case 'Link':
              $dt = t('a web address starting with http://');
              break;
            case 'Money':
              $dt = t('a currency value');
              break;
          }
          $this->form_state->setError($element, t('Please enter @type for %field.', ['@type' => $dt, '%field' => $element['#title']]));
        }
      }
    }
  }

  /**
   * Validate event participants and add line items
   */
  private function validateParticipants() {
    // If we have no valid contacts on the form, don't bother continuing
    if (!$this->existing_contacts) {
      return;
    }
    $count = $this->data['participant_reg_type'] == 'all' ? count($this->existing_contacts) : 1;
    // Collect selected events
    foreach ($this->data['participant'] as $c => $par) {
      if ($this->data['participant_reg_type'] == 'all') {
        $contacts = $this->existing_contacts;
      }
      elseif (isset($this->existing_contacts[$c])) {
        $contacts = [$this->existing_contacts[$c]];
      }
      else {
        continue;
      }
      $participants = current(wf_crm_aval($this->data['contact'], "$c:contact", []));

      $participantName = ($participants['first_name'] ?? '') . ' ' . ($participants['last_name'] ?? '');
      if (!trim($participantName)) {
        $participantName = $participants['webform_label'] ?? NULL;
        if (!empty($this->existing_contacts[$c])) {
          $participantName = $this->utils->wf_crm_apivalues('contact', 'get', $this->existing_contacts[$c], 'display_name')[0];
        }
      }
      // @todo this duplicates a lot in \wf_crm_webform_preprocess::populateEvents
      foreach (wf_crm_aval($par, 'participant', []) as $n => $p) {
        foreach (array_filter(wf_crm_aval($p, 'event_id', [])) as $id_and_type) {
          [$eid] = explode('-', $id_and_type);
          if (is_numeric($eid)) {
            $this->events[$eid]['ended'] = TRUE;
            $this->events[$eid]['title'] = t('this event');
            $this->events[$eid]['count'] = wf_crm_aval($this->events, "$eid:count", 0) + $count;
            $this->line_items[] = [
              'qty' => $count,
              'entity_table' => 'civicrm_participant',
              'event_id' => $eid,
              'contact_ids' => $contacts,
              'unit_price' => $p['fee_amount'] ?? 0,
              'element' => "civicrm_{$c}_participant_{$n}_participant_{$id_and_type}",
              'contact_label' => $participantName,
            ];
          }
        }
      }
    }
    // Subtract events already registered for - this only works with known contacts
    $cids = array_filter($this->existing_contacts);
    if ($this->events && $cids) {
      $status_types = $this->utils->wf_crm_apivalues('participant_status_type', 'get', ['is_counted' => 1, 'return' => 'id']);
      $existing = $this->utils->wf_crm_apivalues('Participant', 'get', [
        'return' => ['event_id', 'contact_id'],
        'event_id' => ['IN' => array_keys($this->events)],
        'status_id' => ['IN' => array_keys($status_types)],
        'contact_id' => ['IN' => $cids],
        'is_test' => 0,
      ]);
      foreach ($existing as $participant) {
        foreach ($this->line_items as $k => &$item) {
          if ($participant['event_id'] == $item['event_id'] && in_array($participant['contact_id'], $item['contact_ids'])) {
            unset($this->line_items[$k]['contact_ids'][array_search($participant['contact_id'], $item['contact_ids'])]);
            if (!(--$item['qty'])) {
              unset($this->line_items[$k]);
            }
            // Update the count in $this->events elements.
            if (!(--$this->events[$participant['event_id']]['count'])) {
              unset($this->events[$participant['event_id']]);
            }
          }
        }
      }
    }
    $this->loadEvents();
    // Add event info to line items
    $format = wf_crm_aval($this->data['reg_options'], 'title_display', 'title');
    // Get the Event Fee Financial Type Id if active, otherwise get the first active Financial Type Id
    if ($this->line_items) {
      $eventFTId = $this->utils->wf_crm_apivalues('FinancialType', 'get', [
        'sequential' => 1,
        'return' => ["id"],
        'name' => "Event Fee",
        'is_active' => 1,
      ])[0]['id'] ?? NULL;
      if (empty($eventFTId)) {
        $eventFTId = $this->utils->wf_crm_apivalues('FinancialType', 'get', [
          'sequential' => 1,
          'return' => ["id"],
          'is_active' => 1,
          'options' => ['limit' => 1],
        ])[0]['id'];
      }
    }
    foreach ($this->line_items as &$item) {
      $label = empty($item['contact_label']) ? '' : "{$item['contact_label']} - ";
      $item['label'] = $label . $this->utils->wf_crm_format_event($this->events[$item['event_id']], $format);
      $item['financial_type_id'] = wf_crm_aval($this->events[$item['event_id']], 'financial_type_id', $eventFTId);
    }
    // Form Validation
    if (!empty($this->data['reg_options']['validate'])) {
      foreach ($this->events as $eid => $event) {
        if ($event['ended']) {
          $this->form_state->setErrorByName($eid, t('Sorry, you can no longer register for %event.', ['%event' => $event['title']]));
        }
        elseif (!empty($event['max_participants']) && $event['count'] > $event['available_places']) {
          if (!empty($event['is_full'])) {
            $this->form_state->setErrorByName($eid, t('%event : @text', ['%event' => $event['title'], '@text' => $event['event_full_text']]));
          }
          else {
            $registrations = \Drupal::translation()->formatPlural($event['count'], '1 person', '@count people');
            $this->form_state->setErrorByName($eid, \Drupal::translation()->formatPlural($event['available_places'],
              'Sorry, you tried to register @registrations for %event but there is only 1 space remaining.',
              'Sorry, you tried to register @registrations for %event but there are only @count spaces remaining.',
              ['%event' => $event['title'], '@registrations' => $registrations]));
          }
        }
      }
    }
  }

  /**
   * Load entire webform submission during validation, including contact ids and $this->data
   * Used when validation for one page needs access to submitted values from other pages
   */
  private function loadMultiPageData() {
    if (!$this->multiPageDataLoaded) {
      $this->multiPageDataLoaded = TRUE;
      /*
       * This feels like an old/weird hack to bypass form API/webform funk in D7.
      if (!empty($this->form_state['storage']['submitted']) && wf_crm_aval($this->form_state, 'storage:page_num', 1) > 1) {
        $this->rawValues += $this->form_state['storage']['submitted'];
        $this->crmValues = wf_crm_enabled_fields($this->node, $this->rawValues);
      }
      */

      // Check how many valid contacts we have
      foreach ($this->data['contact'] as $c => $contact) {
        // Check if we have a contact_id
        $fid = "civicrm_{$c}_contact_1_contact_existing";
        if ($this->verifyExistingContact(wf_crm_aval($this->crmValues, $fid), $fid)) {
          $this->existing_contacts[$c] = $this->crmValues[$fid];
        }
        // Or else see if enough info was entered to create a contact - use 0 as a placeholder for unknown cid
        elseif ($this->utils->wf_crm_name_field_exists($this->crmValues, $c, $contact['contact'][1]['contact_type'])) {
          $this->existing_contacts[$c] = 0;
        }
      }

      // Fill data array with submitted form values
      $this->fillDataFromSubmission();
    }
  }

  /**
   * Fetch contact ids from "existing contact" fields
   */
  private function getExistingContactIds() {
    foreach ($this->enabled as $field_key => $fid) {
      if (substr($field_key, -8) === 'existing') {
        [, $c, ] = explode('_', $field_key, 3);
        $cid = wf_crm_aval($this->submissionValue($fid), 0);
        $this->ent['contact'][$c]['id'] = $this->verifyExistingContact($cid, $field_key);
        if ($this->ent['contact'][$c]['id']) {
          $this->existing_contacts[$c] = $cid;
        }
      }
    }
  }

  /**
   * Ensure we have a valid contact id in a contact ref field
   * @param $cid
   * @param $fid
   * @return int
   */
  private function verifyExistingContact($cid, $fid) {
    if ($this->utils->wf_crm_is_positive($cid) && !empty($this->enabled[$fid])) {
      $contactComponent = \Drupal::service('webform_civicrm.contact_component');
      $component = $this->node->getElement($fid);
      $filters = $contactComponent->wf_crm_search_filters($this->node, $component);
      // Verify access to this contact
      if ($contactComponent->wf_crm_contact_access($component, $filters, $cid) !== FALSE) {
        return $cid;
      }
    }
    return 0;
  }

  /**
   * Check if at least one required field was filled for a contact
   * @param array $contact
   * @return bool
   */
  private function isContactEmpty($contact) {
    $contact_type = $contact['contact'][1]['contact_type'];
    foreach ($this->utils->wf_crm_required_contact_fields($contact_type) as $f) {
      if (!empty($contact[$f['table']][1][$f['name']])) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Search for an existing contact using configured deupe rule
   * @param array $contact
   * @return int
   */
  private function findDuplicateContact($contact) {
    // This is either a default type (Unsupervised or Supervised) or the id of a specific rule
    $rule = wf_crm_aval($contact, 'matching_rule', 'Unsupervised', TRUE);
    if ($rule) {
      $contact['contact'][1]['contact_type'] = ucfirst($contact['contact'][1]['contact_type']);
      $params = [
        'check_permissions' => FALSE,
        'sequential' => TRUE,
        'rule_type' => is_numeric($rule) ? NULL : $rule,
        'dedupe_rule_id' => is_numeric($rule) ? $rule : NULL,
        'match' => [],
      ];
      // If sharing an address, use the master
      if (!empty($contact['address'][1]['master_id'])) {
        $m = $contact['address'][1]['master_id'];
        // If master address is exposed to the form, use it
        if (!empty($this->data['contact'][$m]['address'][1])) {
          $contact['address'][1] = $this->data['contact'][$m]['address'][1];
        }
        // Else look up the master contact's address
        elseif (!empty($this->existing_contacts[$m])) {
          $masters = $this->utils->wf_crm_apivalues('address', 'get', [
            'contact_id' => $this->ent['contact'][$m]['id'],
            'sort' => 'is_primary DESC',
            'limit' => 1,
          ]);
          if (!empty($masters)) {
            $contact['address'][1] = reset($masters);
          }
        }
      }
      foreach ($contact as $table => $fields) {
        if (is_array($fields) && !empty($fields[1])) {
          $params['match'] += $fields[1];
        }
      }
      // Pass custom params to deduper
      if ($params['match']) {
        $dupes = $this->utils->wf_crm_apivalues('Contact', 'duplicatecheck', $params);
      }
    }
    return $dupes[0]['id'] ?? 0;
  }

  /**
   * Create a new contact
   * @param array $contact
   * @return int
   */
  private function createContact($contact) {
    $params = $contact['contact'][1];
    // CiviCRM API is too picky about this, imho
    $params['contact_type'] = ucfirst($params['contact_type']);
    unset($params['contact_id'], $params['id']);
    if (!isset($params['source'])) {
      $params['source'] = $this->settings['new_contact_source'];
    }
    // If creating individual with no first/last name,
    // set display name and sort_name
    if ($params['contact_type'] == 'Individual' && empty($params['first_name']) && empty($params['last_name'])) {
      $params['display_name'] = $params['sort_name'] = empty($params['nick_name']) ? $contact['email'][1]['email'] : $params['nick_name'];
    }
    $result = $this->utils->wf_civicrm_api('contact', 'create', $params);
    return wf_crm_aval($result, 'id', 0);
  }

  /**
   * Update a contact
   * @param array $contact
   * @param int $c
   */
  private function updateContact($contact, $c) {
    $params = $contact['contact'][1];
    unset($params['contact_type'], $params['contact_id']);
    // Fetch data from existing multivalued fields
    $fetch = $multi = [];
    foreach ($this->all_fields as $fid => $field) {
      if (!empty($field['extra']['multiple']) && substr($fid, 0, 7) == 'contact') {
        [, $name] = explode('_', $fid, 2);
        if ($name != 'privacy' && isset($params[$name])) {
          $fetch["return.$name"] = 1;
          $multi[] = $name;
        }
      }
    }
    // Merge data from existing multivalued fields
    if ($multi) {
      $existing = $this->utils->wf_civicrm_api('contact', 'get', ['id' => $this->ent['contact'][$c]['id']] + $fetch);
      $existing = wf_crm_aval($existing, 'values:' . $this->ent['contact'][$c]['id'], []);
      foreach ($multi as $name) {
        $exist_to_merge = (array) wf_crm_aval($existing, $name, []);
        $exist = array_filter(array_combine($exist_to_merge, $exist_to_merge));
        // Only known contacts are allowed to empty a field
        if (!empty($this->existing_contacts[$c])) {
          foreach ($this->getExposedOptions("civicrm_{$c}_contact_1_contact_$name") as $k => $v) {
            unset($exist[$k]);
          }
        }
        $params[$name] = array_unique(array_map("strtolower", array_merge($params[$name], $exist)));
      }
    }
    $params['id'] = $this->ent['contact'][$c]['id'];
    $this->utils->wf_civicrm_api('contact', 'create', $params);
  }

  /**
   * Save current employer for a contact
   * @param array $contact
   * @param int $cid
   */
  function saveCurrentEmployer($contact, $cid) {
    if ($contact['contact'][1]['contact_type'] == 'individual' && !empty($contact['contact'][1]['employer_id'])) {
      $this->utils->wf_civicrm_api('contact', 'create', [
        'id' => $cid,
        'employer_id' => $contact['contact'][1]['employer_id'],
      ]);
    }
  }

  /**
   * Fill values for hidden ID & CS fields
   * @param int $c
   * @param int $cid
   */
  private function fillHiddenContactFields($cid, $c) {
    $fid = 'civicrm_' . $c . '_contact_1_contact_';
    if (!empty($this->enabled[$fid . 'contact_id'])) {
      $this->submissionValue($this->enabled[$fid . 'contact_id'], $cid);
    }
    if (!empty($this->enabled[$fid . 'user_id'])) {
      $user_id = $this->utils->wf_crm_user_cid($cid, 'contact');
      $user_id = $user_id ? $user_id : '';
      $this->submissionValue($this->enabled[$fid . 'user_id'], $user_id);
    }
    if (!empty($this->enabled[$fid . 'existing'])) {
      $this->submissionValue($this->enabled[$fid . 'existing'], $cid);
    }
    if (!empty($this->enabled[$fid . 'external_identifier']) && !empty($this->existing_contacts[$c])) {
      $exid = $this->utils->wf_civicrm_api('contact', 'get', ['contact_id' => $cid, 'return.external_identifier' => 1]);
      $this->submissionValue($this->enabled[$fid . 'external_identifier'], wf_crm_aval($exid, "values:$cid:external_identifier"));
    }
    if (!empty($this->enabled[$fid . 'cs'])) {
      $cs = $this->submissionValue($this->enabled[$fid . 'cs']);
      $life = !empty($cs[0]) ? intval(24 * $cs[0]) : 'inf';
      $cs = \CRM_Contact_BAO_Contact_Utils::generateChecksum($cid, NULL, $life);
      $this->submissionValue($this->enabled[$fid . 'cs'], $cs);
    }
  }

  /**
   * Save location data for a contact
   * @param array $contact
   * @param int $cid
   * @param int $c
   */
  private function saveContactLocation($contact, $cid, $c) {
    // Check which location_type_id is to be set as is_primary=1;
    $is_primary_address_location_type = wf_crm_aval($contact, 'address:1:location_type_id');
    $is_primary_email_location_type = wf_crm_aval($contact, 'email:1:location_type_id');

    foreach ($this->utils->wf_crm_location_fields() as $location) {
      if (!empty($contact[$location])) {
        $existing = [];
        $params = ['contact_id' => $cid];
        if ($location != 'website') {
          $params['options'] = ['sort' => 'is_primary DESC'];
        }
        // If billing address is submitted during validation phase, ignore updating that value.
        if ($location == 'address' && !empty($this->billing_params['billing_address_id'])) {
          $params['id'] = ['!=' => $this->billing_params['billing_address_id']];
        }
        $result = $this->utils->wf_civicrm_api($location, 'get', $params);
        if (!empty($result['values'])) {
          $contact[$location] = self::reorderLocationValues($contact[$location], $result['values'], $location);
          // start array index at 1
          $existing = array_merge([[]], $result['values']);
        }
        foreach ($contact[$location] as $i => $params) {
          // Substitute county stub ('-' is a hack to get around required field when there are no available counties)
          if (isset($params['county_id']) && $params['county_id'] === '-') {
            $params['county_id'] = '';
          }
          // Check if anything was changed, else skip the update
          if (!empty($existing[$i])) {
            $same = TRUE;
            foreach ($params as $param => $val) {
              if ($val != (string) wf_crm_aval($existing[$i], $param, '')) {
                $same = FALSE;
              }
            }
            if ($same) {
              continue;
            }
          }
          if ($location == 'address') {
            // Store shared addresses for later since we haven't necessarily processed
            // the contact this address is shared with yet.
            if (!empty($params['master_id'])) {
              $this->shared_address[$cid][$i] = [
                'id' => wf_crm_aval($existing, "$i:id"),
                'mc' => $params['master_id'],
                'loc' => $params['location_type_id'],
                'pri' => $params['location_type_id'] == $is_primary_address_location_type,
              ];
              continue;
            }
            // Reset calculated values when updating an address
            $params['master_id'] = $params['geo_code_1'] = $params['geo_code_2'] = 'null';
          }
          $params['contact_id'] = $cid;
          if (!empty($existing[$i])) {
            $params['id'] = $existing[$i]['id'];
          }
          if ($this->locationIsEmpty($location, $params)) {
            // Delete this location if nothing was entered and this is a known contact
            if (!empty($this->existing_contacts[$c]) && !empty($params['id'])) {
              $this->utils->wf_civicrm_api($location, 'delete', $params);
            }
            continue;
          }
          if ($location != 'website') {
            if (empty($params['location_type_id'])) {
              $params['location_type_id'] = wf_crm_aval($existing, "$i:location_type_id", 1);
            }
            $params['is_primary'] = $i == 1 ? 1 : 0;
            // Override that just in cases we know
            if ($location == 'address' && $params['location_type_id'] == $is_primary_address_location_type) {
              $params['is_primary'] = 1;
            }
            if ($location == 'email' && $params['location_type_id'] == $is_primary_email_location_type) {
              $params['is_primary'] = 1;
            }
            if (isset($this->settings["civicrm_{$c}_contact_1_{$location}_is_primary"]) && $this->settings["civicrm_{$c}_contact_1_{$location}_is_primary"] == 0) {
              unset($params['is_primary']);
            }
          }
          $this->utils->wf_civicrm_api($location, 'create', $params);
        }
      }
    }
  }

  /**
   * Save groups and tags for a contact
   * @param array $contact
   * @param int $cid
   * @param int $c
   */
  private function saveGroupsAndTags($contact, $cid, $c) {
    // Process groups & tags
    foreach ($this->all_fields as $fid => $field) {
      [$set, $type] = explode('_', $fid, 2);
      if ($set == 'other') {
        $field_name = 'civicrm_' . $c . '_contact_1_' . $fid;
        if (!empty($contact['other'][1][$type]) || isset($this->enabled[$field_name])) {
          $add = wf_crm_aval($contact, "other:1:$type", []);
          // ToDo - $add should only contain the option(s) selected so unset everything else b/c addOrRemoveMultivaluedData is expecting that and we need this to handle TagSets - this is essentially a fail-safe
          foreach ($this->getExposedOptions($field_name) as $k => $v) {
            if (array_key_exists($k, $add) && $add[$k] === 0) {
              unset($add[$k]);
            }
          }
          $remove = empty($this->existing_contacts[$c]) ? [] : $this->getExposedOptions($field_name, $add);
          $this->addOrRemoveMultivaluedData($field['table'], 'contact', $cid, $add, $remove);
        }
      }
    }
  }

  /**
   * Handle adding/removing multivalued data for a contact/activity/etc.
   * Currently used only for groups and tags, but written with expansion in mind.
   *
   * @param $data_type
   *   'group' or 'tag'
   * @param $entity_type
   *   Parent entity: 'contact' etc.
   * @param $id
   *   Entity id
   * @param $add
   *   Groups/tags to add
   * @param array $remove
   *   Groups/tags to remove
   */
  private function addOrRemoveMultivaluedData($data_type, $entity_type, $id, $add, $remove = []) {
    $confirmations_sent = $existing = $params = [];
    $add = array_combine($add, $add);
    static $mailing_lists = [];

    switch ($data_type) {
      case 'group':
        $api = 'group_contact';
        $params['method'] = substr(t('Webform'), 0, 8);
        break;
      case 'tag':
        $api = 'EntityTag';
        break;
      default:
        $api = $data_type;
    }
    if (!empty($add) || !empty($remove)) {
      // Retrieve current records for this entity
      if ($api == 'EntityTag') {
        $params['entity_id'] = $id;
        $params['entity_table'] = 'civicrm_' . $entity_type;
      }
      elseif ($api == 'group_contact' && $entity_type == 'contact') {
        $params['contact_id'] = $id;
      }
      else {
        $params['entity_id'] = $id;
        $params['entity_type'] = 'civicrm_' . $entity_type;
      }
      $fetch = $this->utils->wf_civicrm_api($api, 'get', $params);
      foreach (wf_crm_aval($fetch, 'values', []) as $i) {
        $existing[] = $i[$data_type . '_id'];
        unset($add[$i[$data_type . '_id']]);
      }
      foreach ($remove as $i => $name) {
        if (!in_array($i, $existing)) {
          unset($remove[$i]);
        }
      }
    }
    if (!empty($add)) {
      // Prepare for sending subscription confirmations
      if ($data_type == 'group' && !empty($this->settings['confirm_subscription'])) {
        // Retrieve this contact's primary email address and perform error-checking
        $result = $this->utils->wf_civicrm_api('email', 'get', ['contact_id' => $id, 'options' => ['sort' => 'is_primary DESC']]);
        if (!empty($result['values'])) {
          foreach ($result['values'] as $value) {
            if (($value['is_primary'] || empty($email)) && strpos($value['email'], '@')) {
              $email = $value['email'];
            }
          }
          $mailer_params = [
            'contact_id' => $id,
            'email' => $email,
          ];
          if (empty($mailing_lists)) {
            $mailing_lists = $this->utils->wf_crm_apivalues('group', 'get', ['visibility' => 'Public Pages', 'group_type' => 2], 'title');
          }
        }
      }
      foreach ($add as $a) {
        $params[$data_type . '_id'] = $mailer_params['group_id'] = $a;
        if ($data_type == 'group' && isset($mailing_lists[$a]) && !empty($email)) {
          $result = $this->utils->wf_civicrm_api('mailing_event_subscribe', 'create', $mailer_params);
          if (empty($result['is_error'])) {
            $confirmations_sent[] = Html::escape($mailing_lists[$a]);
          }
          else {
            $this->utils->wf_civicrm_api($api, 'create', $params);
          }
        }
        else {
          $this->utils->wf_civicrm_api($api, 'create', $params);
        }
      }
      if ($confirmations_sent) {
        \Drupal::messenger()->addStatus(t('A message has been sent to %email to confirm subscription to :group.', ['%email' => $email, ':group' => '' . implode('' . t('and') . '', $confirmations_sent) . '']));
      }
    }
    // Remove data from entity
    foreach ($remove as $a => $name) {
      $params[$data_type . '_id'] = $a;
      $this->utils->wf_civicrm_api($api, 'delete', $params);
    }
    if (!empty($remove) && $data_type == 'group') {
      $display_name = $this->utils->wf_civicrm_api('contact', 'get', ['contact_id' => $id, 'return.display_name' => 1]);
      $display_name = wf_crm_aval($display_name, "values:$id:display_name", t('Contact'));
      \Drupal::messenger()->addStatus(t('%contact has been removed from @group.', ['%contact' => $display_name, '@group' => '<em>' . implode('</em> ' . t('and') . ' <em>', $remove) . '</em>']));
    }
  }

  /**
   * Save relationships for a contact
   *
   * @param array $contact
   * @param int $cid
   * @param int $c
   */
  private function saveRelationships($contact, $cid, $c) {
    // Process relationships
    foreach (wf_crm_aval($contact, 'relationship', []) as $n => $params) {
      $relationship_type_id = (array) wf_crm_aval($params, 'relationship_type_id');

      // Expire un-selected relationships.
      $field_key = "civicrm_{$c}_contact_{$n}_relationship_relationship_type_id";
      $remove = array_keys($this->getExposedOptions($field_key, (array) $params['relationship_type_id']));
      if (!empty($remove)) {
        $this->expireRelationship($remove, $cid, $this->ent['contact'][$n]['id']);
      }

      // Create new relationships.
      if (!empty($relationship_type_id)) {
        foreach ($relationship_type_id as $params['relationship_type_id']) {
          $this->processRelationship($params, $cid, $this->ent['contact'][$n]['id']);
        }
      }
    }
  }

  /**
   * End relationship for a pair of contacts
   *
   * @param $type
   * @param $cid1
   * @param $cid2
   */
  private function expireRelationship($removeTypes, $cid1, $cid2) {
    foreach ($removeTypes as $type) {
      $existing = $this->getRelationship([$type], $cid1, $cid2, TRUE);
      if (empty($existing['id'])) {
        continue;
      }
      $params = [
        'id' => $existing['id'],
        'end_date' => 'now',
        'is_active' => 0,
      ];
      $this->utils->wf_civicrm_api('relationship', 'create', $params);
    }
  }

  /**
   * Add/update relationship for a pair of contacts
   *
   * @param $params
   *   Params array for relationship api
   * @param $cid1
   *   Contact id
   * @param $cid2
   *   Contact id
   */
  private function processRelationship($params, $cid1, $cid2) {
    if (!empty($params['relationship_type_id']) && $cid2 && $cid1 != $cid2) {
      [$type, $side] = explode('_', $params['relationship_type_id']);
      $existing = $this->getRelationship([$params['relationship_type_id']], $cid1, $cid2);
      $perm = wf_crm_aval($params, 'relationship_permission');
      // Swap contacts if this is an inverse relationship
      if ($side == 'b' || ($existing && $existing['contact_id_a'] != $cid1)) {
        [$cid1, $cid2] = [$cid2, $cid1];
        if ($perm == 1 || $perm == 2) {
          $perm = $perm == 1 ? 2 : 1;
        }
      }
      //initialise start_date for create action.
      if (empty($existing) && !array_key_exists('start_date', $params)) {
        $params['start_date'] = 'now';
      }
      $params += $existing;
      $params['contact_id_a'] = $cid1;
      $params['contact_id_b'] = $cid2;
      $params['relationship_type_id'] = $type;
      if ($perm) {
        $params['is_permission_a_b'] = $params['is_permission_b_a'] = $perm == 3 ? 1 : 0;
        if ($perm == 1 || $perm == 2) {
          $params['is_permission_' . ($perm == 1 ? 'a_b' : 'b_a')] = 1;
        }
      }
      unset($params['relationship_permission']);
      $this->utils->wf_civicrm_api('relationship', 'create', $params);
    }
  }

  /**
   * Process event participation for a contact
   * @param int $c
   * @param int $cid
   */
  private function processParticipants($c, $cid) {
    static $registered_by_id = [];
    $n = $this->data['participant_reg_type'] == 'separate' ? $c : 1;
    if ($p = wf_crm_aval($this->data, "participant:$n:participant")) {
      // Fetch existing participant records
      $existing = $this->utils->wf_crm_apivalues('Participant', 'get', [
        'return' => ["event_id"],
        'contact_id' => $cid,
        'is_test' => 0,
      ]);
      $existing = array_column($existing, 'id', 'event_id');
      foreach ($p as $e => $params) {
        $remove = [];
        $fid = "civicrm_{$c}_participant_{$e}_participant_event_id";
        // Automatic status - de-selected events will be cancelled if 'disable_unregister' is not selected
        if (empty($this->data['reg_options']['disable_unregister'])) {
          if (empty($params['status_id'])) {
            foreach ($this->getExposedOptions($fid) as $eid => $title) {
              [$eid] = explode('-', $eid);
              if (isset($existing[$eid])) {
                $remove[$eid] = $title;
              }
            }
          }
        }
        if (!empty($params['event_id'])) {
          $params['contact_id'] = $cid;
          if (empty($params['campaign_id']) || empty($this->all_fields['participant_campaign_id'])) {
            unset($params['campaign_id']);
          }

          // Loop through event ids to support multi-valued form elements
          $this->events = (array) $params['event_id'];
          foreach ($this->events as $i => $id_and_type) {
            if (!empty($id_and_type)) {
              [$eid] = explode('-', $id_and_type);
              $params['event_id'] = $eid;
              unset($remove[$eid], $params['registered_by_id'], $params['id'], $params['source']);
              // Is existing participant?
              if (!empty($existing[$eid])) {
                $params['id'] = $existing[$params['event_id']];
              }
              else {
                if (isset($this->data['contact'][$c]['contact'][1]['source'])) {
                  $params['source'] = $this->data['contact'][$c]['contact'][1]['source'];
                }
                else {
                  $params['source'] = $this->settings['new_contact_source'];
                }
                if ($c > 1 && !empty($registered_by_id[$e][$i])) {
                  $params['registered_by_id'] = $registered_by_id[$e][$i];
                }
              }
              // Automatic status
              if (empty($params['status_id']) && empty($params['id'])) {
                $params['status_id'] = 'Registered';
                // Pending payment status
                if ($this->contributionIsIncomplete && !empty($params['fee_amount'])) {
                  $params['status_id'] = $this->contributionIsPayLater ? 'Pending from pay later' : 'Pending from incomplete transaction';
                }
              }
              // Do not update status of existing participant in "Automatic" mode
              if (empty($params['status_id'])) {
                unset($params['status_id']);
              }
              // Set the currency of the result to the currency type that was submitted.
              if (isset($this->data['contribution'][$n]['currency'])) {
                $params['fee_currency'] = $this->data['contribution'][$n]['currency'];
              }
              $result = $this->utils->wf_civicrm_api('participant', 'create', $params);
              $this->ent['participant'][$n]['id'] = $result['id'];

              // Update line-item
              foreach ($this->line_items as &$item) {
                if ($item['element'] == "civicrm_{$n}_participant_{$e}_participant_{$id_and_type}") {
                  if (empty($item['participant_id'])) {
                    $item['participant_id'] = $item['entity_id'] = $result['id'];
                  }
                  $item['participant_count'] = wf_crm_aval($item, 'participant_count', 0) + 1;
                  break;
                }
              }
              // When registering contact 1, store id to apply to other contacts
              if ($c == 1 && empty($this->data['reg_options']['disable_primary_participant'])) {
                $registered_by_id[$e][$i] = $result['id'];
              }
            }
          }
        }
        foreach ($remove as $eid => $title) {
          $this->utils->wf_civicrm_api('participant', 'create', ['status_id' => "Cancelled", 'id' => $existing[$eid]]);
          \Drupal::messenger()->addStatus(t('Registration cancelled for @event', ['@event' => $title]));
        }
      }
    }
  }

  /**
   * Process memberships for a contact
   * Called during webform submission
   * @param int $c
   * @param int $cid
   */
  private function processMemberships($c, $cid) {
    static $types;
    if (!isset($types)) {
      $types = $this->utils->wf_crm_apivalues('membership_type', 'get');
    }
    $existing = $this->findMemberships($cid);
    foreach (wf_crm_aval($this->data, "membership:$c:membership", []) as $n => $params) {
      $membershipStatus = "";
      $membershipEndDate = "";
      $is_active = FALSE;

      if (empty($params['membership_type_id'])) {
        continue;
      }

      if (empty($params['status_id'])) {
        $params['status_override_end_date'] = '';
      }

      // Search for existing membership to renew - must belong to same domain and organization
      // But not necessarily the same membership type to allow for upsell
      if (!empty($params['num_terms'])) {
        $type = $types[$params['membership_type_id']];
        foreach ($existing as $mem) {
          $existing_type = $types[$mem['membership_type_id']];
          if ($type['domain_id'] == $existing_type['domain_id'] && $type['member_of_contact_id'] == $existing_type['member_of_contact_id']) {
            $params['id'] = $mem['id'];
            // If we have an exact match, look no further
            if ($mem['membership_type_id'] == $params['membership_type_id']) {
              $is_active = $mem['is_active'];
              $membershipStatus = $mem['status'];
              $membershipEndDate = $mem['end_date'];
              break;
            }
          }
        }
      }
      if (empty($params['id'])) {
        if (isset($this->data['contact'][$c]['contact'][1]['source'])) {
          $params['source'] = $this->data['contact'][$c]['contact'][1]['source'];
        }
        else {
          $params['source'] = $this->settings['new_contact_source'];
        }
      }
      // Automatic status
      if (empty($params['status_id'])) {
        unset($params['status_id']);
        // Pending payment status
        if ($this->contributionIsIncomplete && $this->getMembershipTypeField($params['membership_type_id'], 'minimum_fee')) {
          if ($is_active == FALSE) {
            $params['status_id'] = 'Pending';
          } else {
            $params['status_id'] = $membershipStatus;
            $params['end_date'] = $membershipEndDate;
          }
        }
      }
      // Override status
      else {
        $params['is_override'] = 1;
      }
      $params['contact_id'] = $cid;
      // The api won't let us manually set status without this weird param
      $params['skipStatusCal'] = !empty($params['status_id']);
      if (isset($this->ent['contribution_recur'][1]['id'])) {
        $params['contribution_recur_id'] = $this->ent['contribution_recur'][1]['id'];
      }

      $result = $this->utils->wf_civicrm_api('membership', 'create', $params);

      if (!empty($result['id'])) {
        // Issue #2516924 If existing membership create renewal activity
        if (!empty($params['id'])) {
          $membership = $result['values'][$result['id']];
          $actParams = [
            'source_contact_id' => $cid,
            'activity_type_id' => 'Membership Renewal',
            'target_id' => $cid,
            'source_record_id' => $result['id'],
          ];
          $memType = $this->utils->wf_civicrm_api('MembershipType', 'getsingle', ['id' => $membership['membership_type_id']]);
          $memStatus = $this->utils->wf_civicrm_api('MembershipStatus', 'getsingle', ['id' => $membership['status_id']]);
          $actParams['subject'] = ts("%1 - Status: %2", [1 => $memType['name'], 2 => $memStatus['label']]);
          $this->utils->wf_civicrm_api('Activity', 'create', $actParams);
        }

        foreach ($this->line_items as &$item) {
          if ($item['element'] == "civicrm_{$c}_membership_{$n}") {
            $item['membership_id'] = $result['id'];
            break;
          }
        }
      }
    }
  }

  /**
   * Process shared addresses
   */
  private function processSharedAddresses() {
    foreach ($this->shared_address as $cid => $shared) {
      foreach ($shared as $i => $addr) {
        if (!empty($this->ent['contact'][$addr['mc']]['id'])) {
          $masters = $this->utils->wf_civicrm_api('address', 'get', ['contact_id' => $this->ent['contact'][$addr['mc']]['id'], 'options' => ['sort' => 'is_primary DESC']]);
          if (!empty($masters['values'])) {
            $masters = array_values($masters['values']);
            // Pick the address with the same location type; default to primary.
            $params = $masters[0];
            foreach ($masters as $m) {
              if ($m['location_type_id'] == $addr['loc']) {
                $params = $m;
                break;
              }
            }
            $params['master_id'] = $params['id'];
            $params['id'] = $addr['id'];
            $params['contact_id'] = $cid;
            $params['is_primary'] = $addr['pri'];
            $this->utils->wf_civicrm_api('address', 'create', $params);
          }
        }
      }
    }
  }

  /**
   * Save case data
   */
  private function processCases() {
    foreach (wf_crm_aval($this->data, 'case', []) as $n => $data) {
      if (is_array($data) && !empty($data['case'][1]['client_id'])) {
        $params = $data['case'][1];
        //Check if webform is set to update the existing case on contact.
        if (!empty($this->data['case'][$n]['existing_case_status']) && empty($this->ent['case'][$n]['id']) && !empty($this->ent['contact'][$n]['id'])) {
          $existingCase = $this->findCaseForContact($this->ent['contact'][$n]['id'], [
            'case_type_id' => wf_crm_aval($this->data['case'][$n], 'case:1:case_type_id'),
            'status_id' => $this->data['case'][$n]['existing_case_status']
          ]);
          if (!empty($existingCase['id'])) {
            $this->ent['case'][$n]['id'] = $existingCase['id'];
          }
        }
        // Set some defaults in create mode
        // Create duplicate case based on existing case if 'duplicate_case' checkbox is checked
        if (empty($this->ent['case'][$n]['id']) || !empty($this->data['case'][$n]['duplicate_case'])) {
          if (empty($params['case_type_id'])) {
            // Abort if no case type.
            continue;
          }
          // Automatic status... for lack of anything fancier just pick the first option ("Ongoing" on a standard install)
          if (empty($params['status_id'])) {
            $options = $this->utils->wf_crm_apivalues('case', 'getoptions', ['field' => 'status_id']);
            $params['status_id'] = current(array_keys($options));
          }
          if (empty($params['subject'])) {
            $params['subject'] = Html::escape($this->node->get('title'));
          }
          // Automatic creator_id - default to current user or contact 1
          if (empty($data['case'][1]['creator_id'])) {
            if (\Drupal::currentUser()->isAuthenticated()) {
              $params['creator_id'] = $this->utils->wf_crm_user_cid();
            }
            elseif (!empty($this->ent['contact'][1]['id'])) {
              $params['creator_id'] = $this->ent['contact'][1]['id'];
            }
            else {
              // Abort if no creator available
              continue;
            }
          }
        }
        // Update mode
        else {
          $params['id'] = $this->ent['case'][$n]['id'];
          // These params aren't allowed in update mode
          unset($params['creator_id']);
        }

        // If orig_contact_id is missing and there's more than one Contact
        // on the Case, set orig_contact_id to the current ID. Otherwise
        // we get a Case.create error "Case is linked with more than one
        // contact id".
        if (empty($params['orig_contact_id']) && !empty($params['id'])) {
          $caseContactCount = $this->utils->wf_civicrm_api('CaseContact', 'getcount', ['case_id' => $params['id']]);
          if ($caseContactCount > 1 && !empty($params['client_id'])) {
            $params['orig_contact_id'] = current((array) $params['client_id']);
          }
        }

        // Allow "automatic" status to pass-thru
        if (empty($params['status_id'])) {
          unset($params['status_id']);
        }
        $result = $this->utils->wf_civicrm_api('case', 'create', $params);

        // Final processing if save was successful
        if (!empty($result['id'])) {
          // handle case tags
          $this->handleEntityTags('case', $result['id'], $n, $params);
          // Store id
          $this->ent['case'][$n]['id'] = $result['id'];
          $relationshipStartDate = date('Ymd');
          // Save case roles
          foreach ($params as $param => $val) {
            if ($val && strpos($param, 'role_') === 0) {
              foreach ((array) $params['client_id'] as $client) {
                foreach ((array) $val as $contactB) {
                  $relationshipParams = [
                    'relationship_type_id' => substr($param, 5),
                    'contact_id_a' => $client,
                    'contact_id_b' => $contactB,
                    'case_id' => $result['id'],
                    'is_active' => TRUE,
                  ];
                  // We can't create a duplicate relationship so check if active relationship exists first.
                  $existingRelationships = $this->utils->wf_civicrm_api('relationship', 'get', $relationshipParams);
                  if (!empty($existingRelationships['count'])) {
                    // Update the existing active relationship (case role)
                    $relationshipParams['id'] = reset($existingRelationships['values'])['id'];
                  }
                  else {
                    // We didn't used to set start_date - now we do when creating new relationships (case roles)
                    $relationshipParams['start_date'] = $relationshipStartDate;
                  }
                  $this->utils->wf_civicrm_api('relationship', 'create', $relationshipParams);
                }
              }
            }
          }
        }
      }
    }
  }

  /**
   * Save activity data
   */
  private function processActivities() {
    $activities = wf_crm_aval($this->data, 'activity', []);
    foreach ($activities as $n => $data) {
      if (is_array($data)) {
        $params = $data['activity'][1];
        // Create mode
        if (empty($this->ent['activity'][$n]['id'])) {
          // Skip if no activity type
          if (empty($params['activity_type_id'])) {
            continue;
          }
          // Automatic status based on whether activity_date_time is in the future
          if (empty($params['status_id'])) {
            $params['status_id'] = strtotime(wf_crm_aval($params, 'activity_date_time', 'now')) > time() ? 'Scheduled' : 'Completed';
          }
          // Automatic source_contact_id - default to current user or contact 1
          if (empty($data['activity'][1]['source_contact_id'])) {
            if (\Drupal::currentUser()->isAuthenticated()) {
              $params['source_contact_id'] = $this->utils->wf_crm_user_cid();
            }
            elseif (!empty($this->ent['contact'][1]['id'])) {
              $params['source_contact_id'] = $this->ent['contact'][1]['id'];
            }
          }
          // Format details as html
          $this->formatSubmissionDetails($params, $n);
        }
        // Update mode
        else {
          $params['id'] = $this->ent['activity'][$n]['id'];

          // Update details when user has selected he wants to update the details
          if (!empty($data['details']['update_existing'])) {
            // Format details as html
            $this->formatSubmissionDetails($params, $n);
          }
        }
        // Allow "automatic" values to pass-thru if empty
        foreach ($params as $field => $value) {
          if ((isset($this->all_fields["activity_$field"]['empty_option']) || isset($this->all_fields["activity_$field"]['exposed_empty_option'])) && !$value) {
            unset($params[$field]);
          }
        }

        // Handle survey data
        if (!empty($params['survey_id'])) {
          $params['source_record_id'] = $params['survey_id'];
          // Set default subject
          if (empty($params['id']) && empty($params['subject'])) {
            $survey = $this->utils->wf_civicrm_api('survey', 'getsingle', ['id' => $params['survey_id'], 'return' => 'title']);
            $params['subject'] = wf_crm_aval($survey, 'title', '');
          }
        }
        // File on case
        if (!empty($data['case_type_id'])) {
          // Webform case
          if ($data['case_type_id'][0] === '#') {
            $case_num = substr($data['case_type_id'], 1);
            if (!empty($this->ent['case'][$case_num]['id'])) {
              $params['case_id'] = $this->ent['case'][$case_num]['id'];
            }
          }
          // Search for case by criteria
          else {
            $case_contact = $this->ent['contact'][$data['case_contact_id']]['id'];
            if ($case_contact) {
              // Proceed only if this activity is not already filed on a case
              if (empty($params['id']) || !$this->utils->wf_crm_apivalues('case', 'get', ['activity_id' => $params['id']])) {
                $case = $this->findCaseForContact($case_contact, [
                  'status_id' => $data['case_status_id'],
                  'case_type_id' => $data['case_type_id'],
                ]);
                if ($case) {
                  $params['case_id'] = $case['id'];
                }
              }
            }
          }
        }
        $activity = $this->utils->wf_civicrm_api('activity', 'create', $params);
        // Final processing if save was successful
        if (!empty($activity['id'])) {
          // handle activity tags
          $this->handleEntityTags('activity', $activity['id'], $n, $params);
          // Store id
          $this->ent['activity'][$n]['id'] = $activity['id'];
          // Save attachments
          if (isset($data['activityupload'])) {
            $this->processAttachments('activity', $n, $activity['id'], empty($params['id']));
          }
          if (!empty($params['assignee_contact_id'])) {
            if ($this->utils->wf_crm_get_civi_setting('activity_assignee_notification')) {
              // Send email to assignees. TODO: Move to CiviCRM API?
              $assignees = $this->utils->wf_crm_apivalues('contact', 'get', [
                'id' => ['IN' => (array) $params['assignee_contact_id']],
              ]);
              $mail = [];
              foreach ($assignees as $assignee) {
                if (!empty($assignee['email'])) {
                  $mail[$assignee['email']] = $assignee;
                }
              }
              if ($mail) {
                // Include attachments while sending a copy of activity.
                $attachments = \CRM_Core_BAO_File::getEntityFile('civicrm_activity', $activity['id']);
                \CRM_Case_BAO_Case::sendActivityCopy(NULL, $activity['id'], $mail, $attachments, NULL);
              }
            }
          }
        }
      }
    }
  }

  /**
   * Handle adding/updating tags for entities (cases, activity)
   *
   * @param $entityType
   * @param $entityId
   * @param $n
   * @param $params
   */
  public function handleEntityTags($entityType, $entityId, $n, $params) {
    foreach ($this->all_fields as $fid => $field) {
      [$set, $type] = explode('_', $fid, 2);
      if ($set == $entityType && isset($field['table']) && $field['table'] == 'tag') {
        $field_name = 'civicrm_' . $n . '_' . $entityType. '_1_' . $fid;
        if ((isset($params['tag']) || isset($this->enabled[$field_name])) && isset($this->data[$entityType][$n])) {
          $add = wf_crm_aval($this->data[$entityType][$n], $entityType . ":1:$type", []);
          $remove = $this->getExposedOptions($field_name, $add);
          $this->addOrRemoveMultivaluedData('tag', $entityType, $entityId, $add, $remove);
        }
      }
    }
  }

 /**
  * Process the submission into the details of the activity.
  */
 private function formatSubmissionDetails(&$params, $activity_number) {
    // Format details as html
    $params['details'] = nl2br(wf_crm_aval($params, 'details', ''));
    // Add webform results to details
    if (!empty($this->data['activity'][$activity_number]['details']['entire_result'])) {
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder('webform_submission');
      $submission = $view_builder->view($this->submission);
      $params['details'] .=  \Drupal::service('renderer')->renderPlain($submission);
    }
    if (!empty($this->data['activity'][$activity_number]['details']['view_link'])) {
      $params['details'] .= '<p>' . $this->submission->toLink(t('View Webform Submission'), 'canonical', [
        'absolute' => TRUE,
      ])->toString() . '</p>' . \Drupal\Core\Link::fromTextAndUrl('View Webform Submission', $this->submission->getTokenUrl('view'))->toString();
    }
    if (!empty($this->data['activity'][$activity_number]['details']['view_link_secure'])) {
      $params['details'] .= '<p>' . \Drupal\Core\Link::fromTextAndUrl('View Webform Submission', $this->submission->getTokenUrl('view'))->toString() . '</p>';
    }
    if (!empty($this->data['activity'][$activity_number]['details']['edit_link'])) {
      $params['details'] .= '<p>' . $this->submission->toLink(t('Edit Submission'), 'edit-form', [
        'absolute' => TRUE,
      ])->toString() . '</p>';
    }
  }

  /**
   * Save grants
   */
  private function processGrants() {
    foreach (wf_crm_aval($this->data, 'grant', []) as $n => $data) {
      if (is_array($data) && !empty($data['grant'][1]['contact_id'])) {
        $params = $data['grant'][1];
        // Set some defaults in create mode
        if (empty($this->ent['grant'][$n]['id'])) {
          // Automatic status... for lack of anything fancier just pick the first option ("Submitted" on a standard install)
          if (empty($params['status_id'])) {
            $options = $this->utils->wf_crm_apivalues('grant', 'getoptions', ['field' => 'status_id']);
            $params['status_id'] = current(array_keys($options));
          }
          if (empty($params['application_received_date'])) {
            $params['application_received_date'] = 'now';
          }
          if (empty($params['grant_report_received'])) {
            $params['grant_report_received'] = '0';
          }
        }
        // Update mode
        else {
          $params['id'] = $this->ent['grant'][$n]['id'];
        }
        // Allow "automatic" status to pass-thru
        if (empty($params['status_id'])) {
          unset($params['status_id']);
        }
        $result = $this->utils->wf_civicrm_api('grant', 'create', $params);
        // Final processing if save was successful
        if (!empty($result['id'])) {
          // Store id
          $this->ent['grant'][$n]['id'] = $result['id'];
          // Save attachments
          if (isset($data['grantupload'])) {
            $this->processAttachments('grant', $n, $result['id'], empty($params['id']));
          }
        }
      }
    }
  }

  /**
   * Calculate line-items for this webform submission
   */
  private function tallyLineItems() {
    // Contribution
    $fid = 'civicrm_1_contribution_1_contribution_total_amount';
    if (isset($this->enabled[$fid]) || $this->getData($fid) > 0) {
      $this->line_items[] = [
        'qty' => 1,
        'unit_price' => $this->getData($fid),
        'financial_type_id' => wf_crm_aval($this->data, 'contribution:1:contribution:1:financial_type_id'),
        'label' => wf_crm_aval($this->node->getElementsDecodedAndFlattened(), $this->enabled[$fid] . ':#title', t('Contribution')),
        'element' => 'civicrm_1_contribution_1',
        'entity_table' => 'civicrm_contribution',
      ];
    }
    // LineItems
    $fid = 'civicrm_1_lineitem_1_contribution_line_total';
    if (isset($this->enabled[$fid])) {
      foreach ($this->data['lineitem'][1]['contribution'] as $n => $lineitem) {
        $fid = "civicrm_1_lineitem_{$n}_contribution_line_total";
        if ($this->getData($fid) != 0) {
          $this->line_items[] = [
            'qty' => 1,
            'unit_price' => $lineitem['line_total'],
            'financial_type_id' => $lineitem['financial_type_id'],
            'label' => wf_crm_aval($this->node->getElementsDecodedAndFlattened(), $this->enabled[$fid] . ':#title', t('Line Item')),
            'element' => "civicrm_1_lineitem_{$n}",
            'entity_table' => 'civicrm_contribution',
          ];
        }
      }
    }
    // Membership
    foreach (wf_crm_aval($this->data, 'membership', []) as $c => $memberships) {
      if (isset($this->existing_contacts[$c]) && !empty($memberships['number_of_membership'])) {
        foreach ($memberships['membership'] as $n => $membership_item) {
          if (!empty($membership_item['membership_type_id'])) {
            $type = $membership_item['membership_type_id'];
            $price = $this->getMembershipTypeField($type, 'minimum_fee');

            // if number of terms is set, regard membership fee field as price per term
            // if you choose to set dates manually while membership fee field is enabled, take the membership fee as total cost of this membership
            if (isset($membership_item['fee_amount'])) {
              $price = $membership_item['fee_amount'];
              if (empty($membership_item['num_terms'])) {
                $membership_item['num_terms'] = 1;
              }
            }

            $membership_financialtype = $this->getMembershipTypeField($type, 'financial_type_id');
            if (isset($membership_item['financial_type_id']) && $membership_item['financial_type_id'] !== 0 ) {
              $membership_financialtype = $membership_item['financial_type_id'];
            };

            if ($price) {
              if (!empty($this->data['contact'][$c]['contact'][$n])) {
                $member_contact = $this->data['contact'][$c]['contact'][$n];
                if (!empty($member_contact['first_name']) && !empty($member_contact['last_name'])) {
                  $member_name = "{$member_contact['first_name']} {$member_contact['last_name']}";
                }
              }
              // If member name is not entered on the form, retrieve it from civicrm.
              if (empty($member_name)) {
                $member_name = $this->utils->wf_crm_display_name($this->existing_contacts[$c]);
              }
              $this->line_items[] = [
                'qty' => $membership_item['num_terms'],
                'unit_price' => $price,
                'financial_type_id' => $membership_financialtype,
                'label' => $this->getMembershipTypeField($type, 'name') . ": " . $member_name,
                'element' => "civicrm_{$c}_membership_{$n}",
                'entity_table' => 'civicrm_membership',
              ];
            }
          }
        }
      }
    }
    // Calculate totals
    $this->totalContribution = 0;
    $taxRates = \CRM_Core_PseudoConstant::getTaxRates();
    foreach ($this->line_items as &$line_item) {
      // Sales Tax integration
      if (!empty($line_item['financial_type_id'])) {
        $itemTaxRate = isset($taxRates[$line_item['financial_type_id']]) ? $taxRates[$line_item['financial_type_id']] : NULL;
      }
      else {
        $itemTaxRate = $this->tax_rate;
      }

      if ($itemTaxRate !== NULL) {
        $line_item['tax_rate'] = $itemTaxRate;
        $line_item['line_total'] = $line_item['unit_price'] * (int) $line_item['qty'];
        $line_item['tax_amount'] = ($itemTaxRate / 100) * $line_item['line_total'];
        $this->totalContribution += ($line_item['unit_price'] * (int) $line_item['qty']) + $line_item['tax_amount'];
      }
      else {
        $line_item['line_total'] = $line_item['unit_price'] * (int) $line_item['qty'];
        $this->totalContribution += $line_item['line_total'];
      }
    }
    return round($this->totalContribution, 2);
  }

  /**
   * Are billing fields exposed to this webform page?
   * @return bool
   */
  private function isPaymentPage() {
    // @todo see if this needs to be more dynamic.
    return $this->form_state->get('current_page') === 'contribution_pagebreak';
  }

  /**
   * @return bool
   */
  private function isLivePaymentProcessor() {
    if ($this->payment_processor) {
      if ($this->payment_processor['billing_mode'] == self::BILLING_MODE_LIVE) {
        return TRUE;
      }
      // In mixed mode (where there is e.g. a PayPal button + credit card fields) the cc field will contain a placeholder if the button was clicked
      if ($this->payment_processor['billing_mode'] == self::BILLING_MODE_MIXED && wf_crm_aval($_POST, 'credit_card_number') != 'express') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Normalize and validate billing input
   * @return bool
   */
  private function validateBillingFields() {
    $valid = TRUE;
    $params = $card_errors = [];
    $payment_processor_id = NestedArray::getValue($this->data, ['contribution', '1', 'contribution', '1', 'payment_processor_id']);

    $processor = \Civi\Payment\System::singleton()->getById($payment_processor_id);
    $billingAddressFieldsMetadata = [];
    if (method_exists($processor, 'getBillingAddressFieldsMetadata')) {
      // getBillingAddressFieldsMetadata did not exist before 4.7
      $billingAddressFieldsMetadata = $processor->getBillingAddressFieldsMetadata();
    }
    $fields = \CRM_Utils_Array::crmArrayMerge($processor->getPaymentFormFieldsMetadata(), $billingAddressFieldsMetadata);

    $request = \Drupal::request();
    $form_values = array_merge($request->request->all(), $this->form_state->getValues());
    foreach ($form_values as $field => $value) {
      if (empty($value) && isset($fields[$field]) && $fields[$field]['is_required'] !== FALSE) {
        $this->form_state->setErrorByName($field, t(':name field is required.', [':name' => $fields[$field]['title']]));
        $valid = FALSE;
      }
      if (!empty($value) && (array_key_exists($field, $fields) || strpos($field, 'billing_address_') !== false)) {
        $name = str_replace('civicrm_1_contribution_1_contribution_billing_address_', '', $field);
        $params[$name] = $value;
        foreach (["billing_{$name}", "billing_{$name}-5"] as $billingName) {
          if (isset($fields[$billingName])) {
            $params[$billingName] = $value;
          }
        }
      }
    }

    // 4.6 compatibility - some processors (eg. authorizeNet / Paypal require that "year" and "month" are set in params.
    if (isset($params['credit_card_exp_date'])) {
      $params['year'] = \CRM_Core_Payment_Form::getCreditCardExpirationYear($params);
      $params['month'] = \CRM_Core_Payment_Form::getCreditCardExpirationMonth($params);
    }

    // Validate billing details
    if (!empty($this->data['billing']['number_number_of_billing'])) {
      \CRM_Core_Payment_Form::validatePaymentInstrument($payment_processor_id, $params, $card_errors, NULL);
    }
    foreach ($card_errors as $field => $msg) {
      $this->form_state->setErrorByName($field, $msg);
      $valid = FALSE;
    }
    // Email
    $c = $this->getContributionContactIndex();
    for ($i = 1; $i <= $this->data['contact'][$c]['number_of_email']; ++$i) {
      if (!empty($this->crmValues["civicrm_{$c}_contact_{$i}_email_email"])) {
        $params['email'] = $this->crmValues["civicrm_{$c}_contact_{$i}_email_email"];
        break;
      }
    }
    if (empty($params['email']) AND !$processor->supports('NoEmailProvided')) {
      $this->form_state->setErrorByName('billing_email', ts('An email address is required to complete this transaction.'));
      $valid = FALSE;
    }
    if ($valid) {
      $this->billing_params = $params;
    }
    // Since billing fields are not "real" form fields they get cleared if the page reloads.
    // We add a bit of js to fix this annoyance.
    $this->form['#attached']['drupalSettings']['webform_civicrm']['billingSubmission'] = $params;
    return $valid;
  }

  /**
   * Return index value of contribution contact.
   */
  private function getContributionContactIndex() {
    return wf_crm_aval($this->settings, "data:contribution:1:contribution:1:contact_id") ?? wf_crm_aval($this->submissionValue("civicrm_1_contribution_1_contribution_contact_id"), 0) ?? 1;
  }

  /**
   * Create contact 1 if not already existing (required by contribution.transact)
   * @return int
   */
  private function createBillingContact() {
    $i = $this->getContributionContactIndex();
    $cid = wf_crm_aval($this->existing_contacts, $i);
    if (!$cid) {
      $contact = $this->data['contact'][$i];
      // Only use middle name from billing if we are using the rest of the billing name as well
      if (empty($contact['contact'][1]['first_name']) && !empty($this->billing_params['middle_name'])) {
        $contact['contact'][1]['middle_name'] = $this->billing_params['middle_name'];
      }
      $contact['contact'][1] += [
        'first_name' => $this->billing_params['first_name'] ?? NULL,
        'last_name' => $this->billing_params['last_name'] ?? NULL,
      ];
      $cid = $this->findDuplicateContact($contact);
    }
    $address = [
      'street_address' => $this->billing_params['street_address'] ?? NULL,
      'city' => $this->billing_params['city'] ?? NULL,
      'country_id' => $this->billing_params['country_id'] ?? NULL,
      'state_province_id' => wf_crm_aval($this->billing_params, 'state_province_id'),
      'postal_code' => $this->billing_params['postal_code'] ?? NULL,
      'location_type_id' => 'Billing',
    ];
    $email = [
      'email' => $this->billing_params['email'],
      'location_type_id' => 'Billing',
    ];
    if (!$cid) {
      // Current employer must wait for ContactRef ids to be filled
      unset($contact['contact'][1]['employer_id']);
      $cid = $this->createContact($contact);
      $this->billing_contact = $cid;
    }
    else {
      foreach (['address', 'email'] as $loc) {
        $result = $this->utils->wf_civicrm_api($loc, 'get', [
          'contact_id' => $cid,
          'location_type_id' => 'Billing',
        ]);
        // Use first id if we have any results
        if (!empty($result['values'])) {
          $ids = array_keys($result['values']);
          ${$loc}['id'] = $ids[0];
        }
      }
    }
    if ($cid) {
      $address['contact_id'] = $email['contact_id'] = $this->ent['contact'][$i]['id'] = $cid;
      // Don't create a blank billing address.
      if ($address['street_address'] || $address['city'] || $address['country_id'] || $address['state_province_id'] || $address['postal_code']) {
        $this->billing_params['billing_address_id'] = $this->utils->wf_civicrm_api('address', 'create', $address)['id'] ?? NULL;
      }
      if ($email['email'] ?? FALSE) {
        $this->billing_params['billing_email_id'] = $this->utils->wf_civicrm_api('email', 'create', $email)['id'] ?? NULL;
      }
    }
    return $cid;
  }

  /**
   * Execute payment processor transaction
   * This happens during form validation and sets a form error if unsuccessful
   */
  private function submitLivePayment() {
    $contributionParams = $this->contributionParams();

    // Only if #installments = 1, do we process a single transaction/contribution. #installments = 0 (or not set) in CiviCRM Core means open ended commitment;
    $numInstallments = wf_crm_aval($contributionParams, 'installments', NULL, TRUE);
    $frequencyInterval = wf_crm_aval($contributionParams, 'frequency_unit');
    if ($numInstallments != 1 && !empty($frequencyInterval)) {
      $result = $this->contributionRecur($contributionParams);
    }
    else {
      // Not a recur payment, remove any recur fields if present.
      unset($contributionParams['frequency_unit'], $contributionParams['frequency_interval'], $contributionParams['is_recur']);
      $result = $this->utils->wf_civicrm_api('contribution', 'transact', $contributionParams);
    }

    if (empty($result['id'])) {
      if (!empty($result['error_message'])) {
        $this->form_state->setErrorByName('contribution', $result['error_message']);
      }
      else {
        $this->form_state->setErrorByName('contribution', t('Transaction failed. Please verify all billing fields are correct.'));
      }
      return;
    }
    $this->ent['contribution'][1]['id'] = $result['id'];
  }

  /**
   *
   * Generate recurring contribution and transact the first one
   */
  private function contributionRecur($contributionParams, $paymentType = 'live') {
    $numInstallments = wf_crm_aval($contributionParams, 'installments', NULL, TRUE);
    // if ($installments === 0) or $installments is not defined we treat as an open-ended recur
    $contributionFirstAmount = $contributionRecurAmount = $contributionParams['total_amount'];
    $salesTaxFirstAmount = wf_crm_aval($contributionParams, 'tax_amount', NULL, TRUE);
    $nondeductibleFirstAmount = wf_crm_aval($contributionParams, 'non_deductible_amount', NULL, TRUE);
    if ($numInstallments > 0) {
      // DRU-2862396 - we must ensure to only present 2 decimals to CiviCRM:
      $contributionRecurAmount = round(($contributionParams['total_amount'] / $numInstallments), 2, PHP_ROUND_HALF_UP);
      $contributionFirstAmount = $contributionRecurAmount;
      $salesTaxFirstAmount = round(($salesTaxFirstAmount / $numInstallments), 2, PHP_ROUND_HALF_UP);
      $nondeductibleFirstAmount = round(($nondeductibleFirstAmount / $numInstallments), 2, PHP_ROUND_HALF_UP);

      // Calculate the line_items for the first contribution:
      // At this point line_items are set to the full (non-installment) amounts.
      foreach ($this->line_items as $key => $k) {
        $this->line_items[$key]['unit_price'] = round(($k['unit_price'] / $numInstallments), 2, PHP_ROUND_HALF_UP);
        $this->line_items[$key]['line_total'] = round(($k['line_total'] / $numInstallments), 2, PHP_ROUND_HALF_UP);
        $this->line_items[$key]['tax_amount'] = round(($k['tax_amount'] / $numInstallments), 2, PHP_ROUND_HALF_UP);
      }
    }

    // Create Params for Creating the Recurring Contribution Series and Create it
    $contributionRecurParams = [
      'contact_id' => $contributionParams['contact_id'],
      'frequency_interval' => wf_crm_aval($contributionParams, 'frequency_interval', 1),
      'frequency_unit' => wf_crm_aval($contributionParams, 'frequency_unit', 'month'),
      'installments' => $numInstallments,
      'amount' => $contributionRecurAmount,
      'contribution_status_id' => 'In Progress',
      'currency' => $contributionParams['currency'],
      'payment_processor_id' => $contributionParams['payment_processor_id'],
      'financial_type_id' => $contributionParams['financial_type_id'],
    ];

    if (empty($contributionParams['payment_processor_id'])) {
      $contributionRecurParams['payment_processor_id'] = 'null';
    }

    $resultRecur = $this->utils->wf_civicrm_api('ContributionRecur', 'create', $contributionRecurParams);
    $this->ent['contribution_recur'][1]['id'] = $resultRecur['id'];

    // Run the Transaction - and Create the Contribution Record - relay Recurring Series information in addition to the already existing Params [and re-key where needed]; at times two keys are required
    $contributionParams['total_amount'] = $contributionFirstAmount;
    $contributionParams['tax_amount'] = $salesTaxFirstAmount;
    $contributionParams['non_deductible_amount'] = $nondeductibleFirstAmount;
    $additionalParams = [
      'contribution_recur_id' => $resultRecur['id'],
      'contributionRecurID' => $resultRecur['id'],
      'is_recur' => 1,
      'contactID' => $contributionParams['contact_id'],
      'frequency_interval' => wf_crm_aval($contributionParams, 'frequency_interval', 1),
      'frequency_unit' => wf_crm_aval($contributionParams, 'frequency_unit', 'month'),
    ];

    $APIAction = 'transact';
    if (in_array($paymentType, ['deferred', 'ipn'], TRUE)) {
      $APIAction = 'create';
    }
    $result = $this->utils->wf_civicrm_api('contribution', $APIAction, $contributionParams + $additionalParams);

    // If transaction was successful - Update the Recurring Series - currency must be resubmitted or else it will re-default to USD; invoice_id is that of the initiating Contribution
    if (empty($result['error_message'])) {
      $recurParams = [
        'id' => $resultRecur['id'],
        'currency' => $contributionParams['currency'],
        'next_sched_contribution_date' => date("Y-m-d H:i:s", strtotime('+' . $contributionRecurParams['frequency_interval'] . ' ' . $contributionRecurParams['frequency_unit'])),
      ];
      $recurParams['invoice_id'] = $result['values'][$result['id']]['invoice_id'];
      $recurParams['payment_instrument_id'] = $result['values'][$result['id']]['payment_instrument_id'];
      $this->utils->wf_civicrm_api('ContributionRecur', 'create', $recurParams);
    }
    return $result;
  }

  /**
   * Create Pending (Pay Later) Contribution
   */
  private function createDeferredPayment() {
    $this->contributionIsIncomplete = TRUE;

    $this->contributionIsPayLater = FALSE;
    $paymentProcessorID = NestedArray::getValue($this->data, ['contribution', '1', 'contribution', '1', 'payment_processor_id']);
    if(empty($paymentProcessorID)) {
      $this->contributionIsPayLater = TRUE;
    }
    else {
      $paymentProcessorClassName = civicrm_api3('PaymentProcessor', 'getvalue', [
        'return' => 'class_name',
        'id' => $paymentProcessorID,
      ]);

      if ($paymentProcessorClassName === 'Payment_Manual') {
        $this->contributionIsPayLater = TRUE;
      }
    }

    $params = $this->contributionParams();
    $params['contribution_status_id'] = 'Pending';
    $params['is_pay_later'] = $this->contributionIsPayLater;

    $numInstallments = wf_crm_aval($params, 'installments', NULL, TRUE);
    $frequencyInterval = wf_crm_aval($params, 'frequency_unit');
    if ($numInstallments != 1 && !empty($frequencyInterval)) {
      if ($this->contributionIsPayLater) {
        $result = $this->contributionRecur($params, 'deferred');
      }
      elseif (empty($this->isLivePaymentProcessor())) {
        $result = $this->contributionRecur($params, 'ipn');
      }
    }
    if (empty($result['id'])) {
      $result = $this->utils->wf_civicrm_api('contribution', 'create', $params);
    }

    $this->ent['contribution'][1]['id'] = $result['id'];
  }

  /**
   * Call IPN payment processor to redirect to payment site
   */
  private function submitIPNPayment() {
    $params = $this->data['contribution'][1]['contribution'][1];
    $processor_type = $this->utils->wf_civicrm_api('payment_processor', 'getsingle', ['id' => $params['payment_processor_id']]);
    $paymentProcessor = \Civi\Payment\System::singleton()->getById($params['payment_processor_id']);

    // Include billing fields with keys expected by payment processor.
    $billingAddressFieldsMetadata = [];
    if (method_exists($paymentProcessor, 'getBillingAddressFieldsMetadata')) {
      $billingAddressFieldsMetadata = $paymentProcessor->getBillingAddressFieldsMetadata();
      foreach ($params as $key => $value) {
        if (strpos($key, 'billing_address_') !== false) {
          $name = str_replace('billing_address_', '', $key);
          foreach (["billing_{$name}", "billing_{$name}-5"] as $billingName) {
            if (isset($billingAddressFieldsMetadata[$billingName])) {
              $params[$billingName] = $value;
            }
          }
        }
      }
    }
    // Ideally we would pass the correct id for the test processor through but that seems not to be the
    // case so load it here.
    if (!empty($params['is_test'])) {
      $paymentProcessor = \Civi\Payment\System::singleton()->getByName($processor_type['name'], TRUE);
    }
    // Add contact details to params (most processors want a first_name and last_name)
    $i = $this->getContributionContactIndex();
    $contact = $this->utils->wf_civicrm_api('contact', 'getsingle', ['id' => $this->ent['contact'][$i]['id']]);
    $params += $contact;
    $params['contributionID'] = $params['id'] = $this->ent['contribution'][1]['id'];
    if (!empty($this->ent['contribution_recur'][1]['id'])) {
      $params['is_recur'] = TRUE;
      $params['contributionRecurID'] = $this->ent['contribution_recur'][1]['id'];
    }
    // Generate a fake qfKey in case payment processor redirects to contribution thank-you page
    $params['qfKey'] = $this->getQfKey();

    $params['contactID'] = $params['contact_id'];
    $params['currency'] = $params['currencyID'] = wf_crm_aval($this->data, "contribution:1:currency");
    $params['total_amount'] = round($this->totalContribution, 2);
    // Some processors want this one way, some want it the other
    $params['amount'] = $params['total_amount'];
    $numInstallments = wf_crm_aval($params, 'installments', NULL, TRUE);
    $frequencyInterval = wf_crm_aval($params, 'frequency_unit');
    if ($numInstallments == 1 || empty($frequencyInterval)) {
      unset($params['frequency_unit'], $params['frequency_interval'], $params['is_recur']);
    }

    $params['financial_type_id'] = wf_crm_aval($this->data, 'contribution:1:contribution:1:financial_type_id');

    $params['source'] = $this->settings['new_contact_source'];
    $params['item_name'] = $params['description'] = t('Webform Payment: @title', ['@title' => $this->node->label()]);

    if (method_exists($paymentProcessor, 'setSuccessUrl')) {
      $params['successURL'] = $this->getIpnRedirectUrl('success');
      $params['cancelURL'] = $this->getIpnRedirectUrl('cancel');
    }

    $this->form_state->set(['civicrm', 'doPayment'], $params);

  }

  /**
   * @param $type
   * @return string
   */
  public function getIpnRedirectUrl($type) {
    $confirmation_type = $this->node->getSetting('confirmation_type');
    $url = trim($this->node->getSetting('confirmation_url'));
    if ($type === 'cancel') {
      $url = $this->node->toUrl('canonical', ['absolute' => TRUE])->toString();
    }
    elseif ($confirmation_type === WebformInterface::CONFIRMATION_PAGE) {
      $request_handler = \Drupal::getContainer()->get('webform.request');
      $query = ['sid' => $this->submission->id()];
      // Add token if user is not authenticated, inline with 'webform_client_form_submit()'
      if (\Drupal::currentUser()->isAnonymous()) {
        // @todo look up how this works in D8.
        // $query['token'] = webform_get_submission_access_token($this->submission);
      }
      $url = $request_handler->getUrl($this->node, $this->node, 'webform.confirmation', ['query' => $query, 'absolute' => TRUE])->toString();
    }
    else {
      // $parsed = webform_replace_url_tokens($url, $this->node, $this->submission);
      // $parsed[1]['absolute'] = TRUE;
      // $url = url($parsed[0], $parsed[1]);
    }
    return $url;
  }

  /**
   * Format contribution params for transact/pay-later
   * @return array
   */
  private function contributionParams() {
    $params = $this->billing_params + $this->data['contribution'][1]['contribution'][1];
    $params['financial_type_id'] = wf_crm_aval($this->data, 'contribution:1:contribution:1:financial_type_id');
    $params['currency'] = $params['currencyID'] = wf_crm_aval($this->data, "contribution:1:currency");
    $params['skipRecentView'] = $params['skipLineItem'] = 1;

    $i = $this->getContributionContactIndex();
    $params['contact_id'] = $this->ent['contact'][$i]['id'];

    $params['total_amount'] = round($this->totalContribution, 2);

    // Most payment processors expect this (normally be set by contribution page processConfirm)
    $params['contactID'] = $params['contact_id'];

    // Some processors use this for matching and updating the contribution status
    if (!$this->contributionIsPayLater) {
      $params['invoice_id'] = $this->data['contribution'][1]['contribution'][1]['invoiceID'] = md5(uniqid(mt_rand(), TRUE));
    }

    // Sales tax integration
    $params['tax_amount'] = 0;
    if (isset($this->form_state->getStorage()['civicrm']['line_items'])) {
      foreach ($this->form_state->getStorage()['civicrm']['line_items'] as $lineItem) {
        if (!empty($lineItem['tax_amount'])) {
          $params['tax_amount'] += $lineItem['tax_amount'];
        }
      }
      $params['tax_amount'] = round($params['tax_amount'], 2);
    }
    $params['description'] = t('Webform Payment: @title', ['@title' => $this->node->label()]);
    if (!isset($params['source'])) {
      $params['source'] = $this->settings['new_contact_source'];
    }

    $financialTypeId = $params['financial_type_id'];
    $financialTypeDetails = $this->utils->wf_civicrm_api('FinancialType', 'get', ['return' => "is_deductible,name",'id' => $financialTypeId]);
    // Some payment processors expect this to be set (eg. smartdebit)
    $params['financialType_name'] = $financialTypeDetails['values'][$financialTypeId]['name'];
    $params['financialTypeID'] = $financialTypeId;
    // Calculate non-deductible amount for income tax purposes
    if ($financialTypeDetails['values'][$financialTypeId]['is_deductible'] == 1) {
      // deductible
      $params['non_deductible_amount'] = 0;
    } else {
      $params['non_deductible_amount']  = $params['total_amount'];
    }

    // Pass all submitted values to payment processor.
    $request = \Drupal::request();
    foreach ($request->request->all() as $key => $value) {
      if (empty($params[$key])) {
        $params[$key] = $value;
      }
    }

    // Fix bug for testing.
    // @todo Pay Later causes issues as it returns `0`.
    if ($params['is_test'] == 1 && $params['payment_processor_id'] !== '0') {
      $liveProcessorName = $this->utils->wf_civicrm_api('payment_processor', 'getvalue', [
        'id' => $params['payment_processor_id'],
        'return' => 'name',
      ]);
      // Lookup current domain for multisite support
      static $domain = 0;
      if (!$domain) {
        $domain = $this->utils->wf_civicrm_api('domain', 'get', ['current_domain' => 1, 'return' => 'id']);
        $domain = wf_crm_aval($domain, 'id', 1);
      }
      $params['payment_processor_id'] = $this->utils->wf_civicrm_api('payment_processor', 'getvalue', [
        'return' => 'id',
        'name' => $liveProcessorName,
        'is_test' => 1,
        'domain_id' => $domain,
      ]);
    }
    if (empty($params['payment_instrument_id']) && !empty($params['payment_processor_id'])) {
      $params['payment_instrument_id'] = $this->getPaymentInstrument($params['payment_processor_id']);
    }

    // doPayment requries payment_processor and payment_processor_mode fields.
    $params['payment_processor'] = wf_crm_aval($params, 'payment_processor_id');

    if (!empty($params['credit_card_number'])) {
      if (!empty($params['credit_card_type'])) {
        $result = $this->utils->wf_civicrm_api('OptionValue', 'get', [
          'return' => ['value'],
          'option_group_id.name' => 'accept_creditcard',
          'name' => ucfirst($params['credit_card_type']),
        ]);
        if (!empty($result['id'])) {
          $params['card_type_id'] = $result['values'][$result['id']]['value'];
        }
      }
      $params['pan_truncation'] = substr($params['credit_card_number'], -4);
    }

    // Save this stuff for later
    unset($params['soft'], $params['honor_contact_id'], $params['honor_type_id']);
    return $params;
  }

  /**
   * Post-processing of contribution
   * This happens during form post-processing
   */
  private function processContribution() {
    $contribution = $this->data['contribution'][1]['contribution'][1];
    $id = $this->ent['contribution'][1]['id'];
    // Save soft credits
    if (!empty($contribution['soft'])) {
      // Get total amount from total amount after line item calculation
      $amount = $this->totalContribution;

      // Get Default softcredit type
      $default_soft_credit_type = $this->utils->wf_civicrm_api('OptionValue', 'getsingle', [
        'return' => "value",
        'option_group_id' => "soft_credit_type",
        'is_default' => 1,
      ]);

      foreach (array_filter($contribution['soft']) as $cid) {
        $this->utils->wf_civicrm_api('contribution_soft', 'create', [
          'contact_id' => $cid,
          'contribution_id' => $id,
          'amount' => $amount,
          'currency' => wf_crm_aval($this->data, "contribution:1:currency"),
          'soft_credit_type_id' => $default_soft_credit_type['value'],
        ]);
      }
    }
    // Save honoree
    if (!empty($contribution['honor_contact_id']) && !empty($contribution['honor_type_id'])) {
      $this->utils->wf_civicrm_api('contribution_soft', 'create', [
        'contribution_id' => $id,
        'amount' => $contribution['total_amount'],
        'contact_id' => $contribution['honor_contact_id'],
        'soft_credit_type_id' => $contribution['honor_type_id'],
      ]);
    }

    $contributionResult = \CRM_Contribute_BAO_Contribution::getValues(['id' => $id], \CRM_Core_DAO::$_nullArray, \CRM_Core_DAO::$_nullArray);

    // Save line-items
    foreach ($this->line_items as &$item) {
      if (empty($item['line_total']) && $item['entity_table'] != 'civicrm_membership') {
        continue;
      }
      if (empty($item['entity_id'])) {
        $item['entity_id'] = $id;
      }
      // tax integration
      if (empty($item['contribution_id'])) {
        $item['contribution_id'] = $id;
      }
      $priceSetId = 'default_contribution_amount';
      // Membership
      if (!empty($item['membership_id'])) {
        $priceSetId = 'default_membership_type_amount';
        $item['entity_id'] = $item['membership_id'];
        $lineItemArray = $this->utils->wf_civicrm_api('LineItem', 'get', [
          'entity_table' => "civicrm_membership",
          'entity_id' => $item['entity_id'],
        ]);
        if ($lineItemArray['count'] != 0) {
          // We only require first membership (signup) entry to make this work.
          $firstLineItem = array_shift($lineItemArray['values']);

          // Membership signup line item entry.
          // Line Item record is already present for membership by this stage.
          // Just need to upgrade contribution_id column in the record.
          if (!isset($firstLineItem['contribution_id'])) {
            $item['id'] = $firstLineItem['id'];
          }
        }
      }
      $item['price_field_id'] = $this->utils->wf_civicrm_api('PriceField', 'get', [
        'sequential' => 1,
        'price_set_id' => $priceSetId,
        'options' => ['limit' => 1],
      ])['id'] ?? NULL;

      // Save the line_item
      $line_result = $this->utils->wf_civicrm_api('line_item', 'create', $item);
      $item['id'] = $line_result['id'];

      $lineItemRecord = json_decode(json_encode($item), FALSE);
      // Add financial_item and entity_financial_trxn
      // hence that we call \CRM_Financial_BAO_FinancialItem::add() twice,
      // once to create the line item 'total amount' financial_item record and the 2nd
      // one to create the line item 'tax amount' financial_item  record.
      \CRM_Financial_BAO_FinancialItem::add($lineItemRecord, $contributionResult);
      if (!empty($lineItemRecord->tax_amount)) {
        \CRM_Financial_BAO_FinancialItem::add($lineItemRecord, $contributionResult, TRUE);
      }

      // Create participant/membership payment records
      if (isset($item['membership_id']) || isset($item['participant_id'])) {
        $type = isset($item['participant_id']) ? 'participant' : 'membership';
        $this->utils->wf_civicrm_api("{$type}_payment", 'create', [
          "{$type}_id" => $item["{$type}_id"],
          'contribution_id' => $id,
        ]);
      }
    }
  }

  /**
   * @param string $ent - entity type
   * @param int $n - entity number
   * @param int $id - entity id
   * @param bool $new - is this a new object? (should we bother checking for existing data)
   */
  private function processAttachments($ent, $n, $id, $new = FALSE) {
    $attachments = $new ? [] : $this->getAttachments($ent, $id);
    foreach ((array) wf_crm_aval($this->data[$ent], "$n:{$ent}upload:1") as $num => $file_id) {
      if ($file_id) {
        [, $i] = explode('_', $num);
        $dao = new \CRM_Core_DAO_EntityFile();
        if (!empty($attachments[$i])) {
          $dao->id = $attachments[$i]['id'];
        }
        $dao->file_id = $file_id;
        $dao->entity_id = $id;
        $dao->entity_table = "civicrm_$ent";
        $dao->save();
      }
    }
  }

  /**
   * Recursive function to fill ContactRef fields with contact IDs
   *
   * @param $contactPrefillMode
   *   TRUE if only standard custom fields needs to be filled.
   *   This is a pre-fill mode which sets a placeholder value if the referenced contact hasn't been saved yet.
   * @internal param $values null|array
   *   Leave blank - used internally to recurse through data
   * @internal param $depth int
   *   Leave blank - used internally to track recursion level
   */
  private function fillContactRefs($contactPrefillMode = FALSE, $values = NULL, $depth = 0) {
    $order = ['ent', 'c', 'table', 'n', 'name'];
    static $ent = '';
    static $c = '';
    static $table = '';
    static $n = '';
    if ($values === NULL) {
      $values = $this->data;
    }
    foreach ($values as $key => $val) {
      ${$order[$depth]} = $key;
      if ($depth < 4 && is_array($val)) {
        $this->fillContactRefs($contactPrefillMode, $val, $depth + 1);
      }
      elseif ($depth == 4 && $val && wf_crm_aval($this->all_fields, "{$table}_{$name}:data_type") == 'ContactReference') {
        // Standard custom fields are processed with their entity; fields from multi-record sets are processed separately
        $isStandardCustom = substr($name, 0, 6) === 'custom' && !$this->isMultiRecordCustomSet($table);
        if ($contactPrefillMode && (!$isStandardCustom || $ent !== 'contact')) {
          return;
        }
        if (is_array($val)) {
          $this->data[$ent][$c][$table][$n][$name] = [];
          foreach ($val as $v) {
            if (is_numeric($v) && !empty($this->ent['contact'][$v]['id'])) {
              $tableName = $isStandardCustom ? $ent : $table;
              $this->data[$ent][$c][$tableName][$n][$name][] = $this->ent['contact'][$v]['id'];
            }
          }
        }
        else {
          // Unset val so it is not mistaken for a contact id
          $this->data[$ent][$c][$table][$n][$name] = NULL;

          if (!empty($this->ent['contact'][$val]['id'])) {
            $tableName = $isStandardCustom ? $ent : $table;
            $this->data[$ent][$c][$tableName][$n][$name] = $this->ent['contact'][$val]['id'];
          }
          elseif ($contactPrefillMode) {
            $this->data[$ent][$c]['update_contact_ref'][$n][$name] = $val;
          }
        }
      }
    }
  }

  /**
   * Fill data array with submitted form values
   */
  private function fillDataFromSubmission() {
    foreach ($this->enabled as $field_key => $fid) {
      $val = (array) $this->submissionValue($fid);
      $customValue = NULL;
      // If value is null then it was hidden by a webform conditional rule - skip it
      if ($val !== NULL && $val !== [NULL]) {
        [ , $c, $ent, $n, $table, $name] = explode('_', $field_key, 6);
        // The following are not strictly CiviCRM fields, so ignore
        if (in_array($name, ['existing', 'fieldset', 'createmode'])) {
          continue;
        }
        // Check to see if configured to ignore hidden field value(s)
        if ($this->isFieldHiddenByExistingContactSettings($ent, $c, $table, $n, $name, TRUE)) {
          continue;
        }
        $field = $this->all_fields[$table . '_' . $name];
        $component = $this->node->getElementDecoded($field_key);
        // Ignore values from hidden fields
        if ($field['type'] === 'hidden') {
          continue;
        }
        // Translate privacy options into separate values
        if ($name === 'privacy') {
          foreach (array_keys($this->getExposedOptions($field_key)) as $key) {
            $this->data[$ent][$c][$table][$n][$key] = in_array($key, $val);
          }
          continue;
        }
        $dataType = wf_crm_aval($field, 'data_type');
        if (!empty($field['extra']['multiple'])) {
          if ($val === ['']) {
            $val = [];
          }
          // Merge with existing data
          if (!empty($this->data[$ent][$c][$table][$n][$name]) && is_array($this->data[$ent][$c][$table][$n][$name])) {
            $val = array_unique(array_merge($val, $this->data[$ent][$c][$table][$n][$name]));
          }
          if (substr($name, 0, 6) === 'custom' || ($table == 'other' && in_array($name, ['group', 'tag']))) {
            $val = array_filter($val);
            if ($name === 'group') {
              unset($val['public_groups']);
            }
          }

          // We need to handle items being de-selected too and provide an array to pass to Entity.create API
          // Extract a list of available items
          $exposedOptions = $this->getExposedOptions($field_key);
          $customValue = [];
          foreach ($exposedOptions as $itemName => $itemLabel) {
            if (in_array($itemName, $val)) {
              $customValue[] = $itemName;
            }
          }
          // Implode data that will be stored as a string
          if ($table !== 'other' && $name !== 'event_id' && $name !== 'relationship_type_id' && $table !== 'contact' && $dataType != 'ContactReference' && strpos($name, 'tag') !== 0) {
            $val = \CRM_Utils_Array::implodePadded($val);
          }
        }
        // If this is a single-static radio button, set it to a non array value
        // since civicrm api doesn't expect array for a radio field.
        elseif (empty($component['#extra']['multiple']) && $this->utils->hasMultipleValues($component)) {
          $val = $val[0] ?? NULL;
        }
        elseif ($name === 'image_url') {
          if (empty($val[0]) || !($val = $this->getDrupalFileUrl($val[0]))) {
            // This field can't be emptied due to the nature of file uploads
            continue;
          }
        }
        elseif ($dataType == 'File') {
          if (empty($val[0]) || !($val = $this->saveDrupalFileToCivi($val[0]))) {
            // This field can't be emptied due to the nature of file uploads
            continue;
          }
        }
        elseif ($field['type'] === 'date') {
          $val = empty($val[0]) ? '' : str_replace('-', '', $val[0]);
          // Add time field value
          $time = wf_crm_aval($this->data, "$ent:$c:$table:$n:$name", '');
          // Remove default date if it has been added
          if (strlen($time) == 14) {
            $time = substr($time, -6);
          }
          $val .= $time;
        }
        // The admin can change a number field to use checkbox/radio/select/grid widget and we'll sum the result
        elseif ($field['type'] === 'number' || $field['type'] === 'civicrm_number') {
          $sum = 0;
          foreach ((array) $val as $k => $v) {
            // Perform multiplication across grid elements
            if (is_numeric($k) && $component['#type'] === 'grid') {
              $v *= $k;
            }
            if (is_numeric($v)) {
              $sum += $v;
            }
          }
          // Do not constrain to only allow positive amounts [note fields like contribution_amount come with a minimum 0 value - so admin must be specific/ok this]
          $val = $sum;
        }
        else {
          $val = is_array($val) ? reset($val) : $val;
          // Trim whitespace from user input
          if (is_string($val)) {
            $val = trim($val);
          }
        }
        if (is_string($val) && '' !== $val && $field['type'] === 'autocomplete') {
          $options = $this->utils->wf_crm_field_options($component, '', $this->data);
          $val = array_search($val, $options);
        }

        // Only known contacts are allowed to empty a field
        if (($val !== '' && $val !== NULL && $val !== []) || !empty($this->existing_contacts[$c])) {
          $this->data[$ent][$c][$table][$n][$name] = $val;
          if (substr($name, 0, 6) === 'custom' && !$this->isMultiRecordCustomSet($table)) {
            $this->data[$ent][$c][$ent][$n][$name] = $customValue ?? $val;
          }
        }
      }
    }
  }

  /**
   * Test whether a field has been hidden due to existing contact settings
   * @param $ent
   * @param $c
   * @param $table
   * @param $n
   * @param $name
   * @param $checkSubmitDisabledSetting
   *   Checks if 'Submit Disabled' setting should be considered
   *
   * @return bool
   */
  private function isFieldHiddenByExistingContactSettings($ent, $c, $table, $n, $name, $checkSubmitDisabledSetting = FALSE) {
    if ($ent === 'contact' && isset($this->enabled["civicrm_{$c}_contact_1_contact_existing"])) {
      $component = $this->node->getElement("civicrm_{$c}_contact_1_contact_existing");
      $existing_contact_val = $this->submissionValue($component['#form_key']);
      // Fields are hidden if value is empty (no selection) or a numeric contact id
      if (!$existing_contact_val[0] || is_numeric($existing_contact_val[0])) {
        $type = ($table == 'contact' && strpos($name, 'name')) ? 'name' : $table;
        $component += ['#hide_fields' => []];
        if (in_array($type, $component['#hide_fields'])) {
          $value = wf_crm_aval($this->loadContact($c), "$table:$n:$name");
          // Check to see if configured to Submit disabled field value(s)
          if ($checkSubmitDisabledSetting && !empty($component['#submit_disabled'])) {
            // If field is disabled on the webform, do not overwrite existing values on the contact.
            if (!empty($value) && !empty($this->submission)) {
              $fieldKey = implode('_', ['civicrm', $c, $ent, $n, $table, $name]);
              $data = $this->submission->getData();
              if (isset($data[$fieldKey])) {
                $data[$fieldKey] = $value;
                $this->submission->setData($data);
              }
            }
            else {
              return FALSE;
            }
          }
          // With the no_hide_blank setting we must load the contact to determine if the field was hidden
          if (wf_crm_aval($component, '#no_hide_blank')) {
            // @todo this method doesn't exist?
            return !(!$value && !is_numeric($value));
          }

          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Test if any relevant data has been entered for a location
   * @param string $location
   * @param array $params
   * @return bool
   */
  private function locationIsEmpty($location, $params) {
    switch ($location) {
      case 'address':
        return empty($params['street_address'])
          && empty($params['city'])
          && empty($params['state_province_id'])
          && empty($params['country_id'])
          && empty($params['postal_code'])
          && (empty($params['master_id']) || $params['master_id'] == 'null');
      case 'website':
        return empty($params['url']);
      case 'im':
        return empty($params['name']);
      default:
        return empty($params[$location]);
    }
  }

  /**
   * Clears an error against a form element.
   * Used to disable validation when this module hides a field
   * @see https://api.drupal.org/comment/49163#comment-49163
   *
   * @param $name string
   */
  private function unsetError($name) {
    $errors = &drupal_static('form_set_error', []);
    $removed_messages = [];
    if (isset($errors[$name])) {
      $removed_messages[] = $errors[$name];
      unset($errors[$name]);
    }
    $_SESSION['messages']['error'] = array_diff($_SESSION['messages']['error'], $removed_messages);
    if (empty($_SESSION['messages']['error'])) {
      unset($_SESSION['messages']['error']);
    }
  }

  /**
   * Get or set a value from a webform submission
   * Always use \Drupal\webform\WebformSubmissionInterface $webform_submission [more reliable according to JR]
   *
   * @param $fid
   *   Numeric webform component id
   * @param $value
   *   Value to set - leave empty to get a value rather than setting it
   *
   * @return array|null field value if found
   */
  protected function submissionValue($fid, $value = NULL) {
    if (!empty($this->form_state)) {
      $form_object = $this->form_state->getFormObject();
      /** @var \Drupal\webform\WebformSubmissionInterface $webform_submission */
      $webform_submission = $form_object->getEntity();
      $data = $webform_submission->getData();
    }
    else {
      $webform_submission = $this->submission;
      $data = $this->submission->getData();
      $webform_submission = $this->submission;
    }

    if (!isset($data[$fid])) {
      return [NULL];
    }

    // Expects an array.
    $field = (array) $data[$fid];
    // During submission preprocessing this is used to alter the submission
    if ($value !== NULL) {
      $data[$fid] = $value;
      $webform_submission->setData($data);
      return $value;
    }
    return $field;
  }

  /**
   * Identifies contact 1 as acting user for CiviCRM's advanced logging
   */
  public function setLoggingContact() {
    if (!empty($this->ent['contact'][1]['id']) && \Drupal::currentUser()->isAnonymous()) {
      \CRM_Core_DAO::executeQuery('SET @civicrm_user_id = %1', [1 => [$this->ent['contact'][1]['id'], 'Integer']]);
    }
  }

  /**
   * Reorder submitted location values according to existing location values
   *
   * @param array $submittedLocationValues
   * @param array $existingLocationValues
   * @param string $entity
   * @return array
   */
  protected function reorderLocationValues($submittedLocationValues, $existingLocationValues, $entity) {
    $reorderedArray = [];
    $index = 1;
    $entityTypeIdIndex = $entity.'_type_id';
    $entity = $entity == 'website' ? 'url' : $entity; // for website only

    foreach ($existingLocationValues as $eValue) {
      $existingLocationTypeId = $entity != 'url' ? $eValue['location_type_id'] : NULL;
      $existingEntityTypeId = isset($eValue[$entityTypeIdIndex]) ? $eValue[$entityTypeIdIndex] : NULL;

      if (!empty($existingEntityTypeId)) {
        $reorderedArray[$index][$entityTypeIdIndex] = $existingEntityTypeId;
      }
      elseif (!empty($existingLocationTypeId)) {
        $reorderedArray[$index]['location_type_id'] = $existingLocationTypeId;
      }

      if (!empty($eValue[$entity])) {
        $reorderedArray[$index][$entity] = $eValue[$entity];
      }

      // address field contain many sub fields and should be handled differently
      if ($entity != 'address')  {
        $submittedLocationValues = self::unsetEmptyValueIndexes($submittedLocationValues, $entity);
        $reorderedArray[$index][$entity] = wf_crm_aval($eValue, $entity);
      }
      else {
        foreach ($this->utils->wf_crm_address_fields() as $field)  {
          if ($field != 'location_type_id') {
            $reorderedArray[$index][$field] = isset($eValue[$field]) ? $eValue[$field] : '';
          }
        }
      }

      foreach ($submittedLocationValues as $key => $sValue) {
        $sLocationTypeId = isset($sValue['location_type_id']) ? $sValue['location_type_id'] : NULL;
        $sEntityTypeId = isset($sValue[$entityTypeIdIndex]) ? $sValue[$entityTypeIdIndex] : NULL;

        if (($existingLocationTypeId == $sLocationTypeId && empty($sEntityTypeId))
            ||
           ($existingEntityTypeId == $sEntityTypeId && empty($sLocationTypeId))
            ||
           ($existingLocationTypeId == $sLocationTypeId && $existingEntityTypeId == $sEntityTypeId)) {

          // address field contain many sub fields and should be handled differently
          if ($entity != 'address')  {
            $reorderedArray[$index] = $sValue;
            unset($submittedLocationValues[$key]);
            break;

          }
          else {
            foreach ($this->utils->wf_crm_address_fields() as $field)  {
              if (isset($sValue[$field]) && $field != 'location_type_id')  {
                $reorderedArray[$index][$field] = $sValue[$field];
              }
            }
          }
          unset($submittedLocationValues[$key]);
        }
      }
      $index++;
    }

    // handle remaining values
    if (!empty($submittedLocationValues)) {
      // cannot use array_push, array_merge because preserving array keys is important
      foreach ($submittedLocationValues as $sValue) {
        $reorderedArray[] = $sValue;
      }
    }

    return $reorderedArray;
  }

  /**
   * @param array $contact
   * @param int $index
   * @param int $cid
   */
  private function saveMultiRecordCustomData($contact, $index, $cid) {
    $existingCustomData = NULL;
    $inserts = [];
    $saveParams = ['id' => $cid];
    foreach ($contact as $table => $items) {
      if (is_array($items) && $items && $this->isMultiRecordCustomSet($table)) {
        $existingCustomData = $existingCustomData ?? $this->getCustomData($cid, 'contact', FALSE, $index);
        $existing = $existingCustomData[$table] ?? [];
        // Index existing ids beginning with 1
        $existingIds = array_slice(array_merge([NULL], array_keys($existing)), 1, NULL, TRUE);
        $newIndex = 0;
        foreach ($items as $i => $item) {
          $id = $existingIds[$i] ?? --$newIndex;
          if ($id > 0) {
            $this->ent['contact'][$index][$table][$i] = $existingIds[$i];
          }
          else {
            $inserts[$table][] = $i;
          }
          foreach ($item as $field => $val) {
            $saveParams[$field . '_' . $id] = $val;
          }
        }
      }
    }
    if (count($saveParams) > 1) {
      $utils = \Drupal::service('webform_civicrm.utils');
      $utils->wf_civicrm_api('Contact', 'create', $saveParams);
      // FIXME: This sucks, but APIv3 does not return the id of newly inserted custom data
      foreach ($inserts as $table => $newRecords) {
        $dbTable = \CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', str_replace('cg', '', $table), 'table_name');
        $maxId = \CRM_Core_DAO::singleValueQuery("SELECT MAX(id) FROM $dbTable");
        foreach ($newRecords as $count => $i) {
          $this->ent['contact'][$index][$table][$i] = $maxId - count($newRecords) + $count + 1;
        }
      }
    }
  }

  /**
   * Save custom contact reference created
   * during the current submission of the webform.
   *
   * @param array $params
   * @param int $cid
   */
  private function saveContactRefs($params, $cid): void {
    $updateParams = [
      'id' => $cid,
    ];
    $skipKeys = [];
    foreach ($params['update_contact_ref'] as $n => $refKeys) {
      foreach ($refKeys as $refKey => $val) {
        // Skip contact ref that doesn't have a valid contact ids.
        if (empty($this->ent['contact'][$val]['id'])) {
          continue;
        }
        foreach ($params['contact'] as $contactParams) {
          foreach ($contactParams as $key => $value) {
            if (strpos($key, "{$refKey}_") === 0 && !isset($updateParams[$key]) && !in_array($key, $skipKeys)) {
              $updateParams[$key] = $value;
            }
          }
        }
      }
    }
    if (count($updateParams) > 1) {
      $this->utils->wf_civicrm_api('contact', 'create', $updateParams);
    }
  }

  private function unsetEmptyValueIndexes($values, $entity) {
    foreach ($values as $k => $v) {
      if (!isset($v[$entity])) {
        unset($values[$k]);
      }
    }

    return $values;
  }

  /**
   * Retrieve payment instrument.
   *
   * @param int $paymentProcessorId
   */
  private function getPaymentInstrument($paymentProcessorId) {
    $processor = \Civi\Payment\System::singleton()->getById($paymentProcessorId);
    return $processor->getPaymentInstrumentID();
  }

  /**
   * Check if a set of data is for a multi-record custom field
   * @param $key e.g. "cg5"
   * @return bool
   */
  private function isMultiRecordCustomSet($key) {
    return strpos($key, 'cg') === 0 && ($this->all_sets[$key]['max_instances'] ?? 1) > 1;
  }

}
