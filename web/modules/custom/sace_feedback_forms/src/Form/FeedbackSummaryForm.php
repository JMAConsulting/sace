<?php

namespace Drupal\sace_feedback_forms\Form;

use Drupal\sace_feedback_forms\Utils;
use Drupal\sace_feedback_forms\TokenReplacement;
use Drupal\webform\Utility\WebformFormHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace Feedback Summary Form
 */
class FeedbackSummaryForm extends FormBase
{

  protected const UNASSIGNED_USER_CONTACT_ID = 860; // = Unassigned User Contact

  /**
   * The CiviCRM service.
   *
   * @var \Drupal\civicrm\Civicrm
   */
  protected $civicrm;

  protected int $bookingId;

  protected array $bookingDetails;

  protected array $summaryFields = [];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sace_feedback_summary_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $bookingId = NULL) {
    $this->bookingId = $bookingId;

    $this->getBookingDetails();

    $this->getBookingContacts();

    $form['summary_header'] = ['#type' => 'fieldset'];

    $this->addBookingDetailsIntro($form['summary_header']);

    $this->addFeedbackCounts($form['summary_header']);

    $this->addSummaryFields($form);

    $this->replaceTokens($form);

    $this->addVerifiedByField($form);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  protected function getBookingDetails() {
    $this->bookingDetails = (array) \Civi\Api4\Activity::get(FALSE)
      // used in addBookingDetailsIntro
      ->addSelect('activity_type_id', 'activity_date_time', 'duration', 'Booking_Information.Youth_or_Adult', 'Booking_Information.Online_Courses', 'Booking_Information.Privilege_and_Oppression_Content', 'Booking_Information.Resources_Content', 'Booking_Information.Support_Content', 'Booking_Information.Presentation_topics', 'Booking_Information.Presentation_topics:label', 'Booking_Information.Safer_Spaces_Content', 'Booking_Information.Facilitating_Program', 'Booking_Information.Presentation_Method', 'Booking_Information.Presentation_custom', 'Booking_Information.Audience', 'Booking_Information.Facilitating_Program')
      // used in addFeedbackCounts
      ->addSelect('Booking_Information.Number_of_Participants_per_course')
      // used in deriveBookingTopic
      ->addSelect('Booking_Information.Online_Courses', 'Booking_Information.Presentation_topics:label', 'Booking_Information.Presentation_custom')
      ->addWhere('id', '=', $this->bookingId)
      ->execute()
      ->first();

    $this->bookingDetails['topic'] = Utils::deriveBookingTopic($this->bookingDetails);
  }

  protected function getBookingContacts() {
    $activityContacts = \Civi\Api4\ActivityContact::get(FALSE)
      ->addWhere('activity_id', '=', $this->bookingId)
      ->addSelect('contact_id.display_name', 'record_type_id:name')
      ->execute();

    $namesByType = [];

    foreach ($activityContacts as $record) {
      $type = $record['record_type_id:name'];
      $namesByType[$type] ??= [];
      $namesByType[$type][] = $record['contact_id.display_name'];
    }

    $this->bookingDetails['target_contacts'] = $namesByType['Activity Targets'];
    $this->bookingDetails['assignee_contacts'] = $namesByType['Activity Assignees'];
  }

  protected function addBookingDetailsIntro(array &$form) {
    $details = [
      "Booking Reference ID" => $this->bookingId,
      "Presentation Topic" => $this->bookingDetails['topic'],
// Sexual Harassment, Sexual Assault & Consent, Non-Consensual Photo Sharing
      //"Presentation Topic Detail" => '',
      "With" => implode(', ', $this->bookingDetails['target_contacts']),
      //"Postal Code" => $this->bookingDetails['postal_code'],
//     T6H 3W8
      "Date" => \DateTime::createFromFormat("Y-m-d H:i:s", $this->bookingDetails['activity_date_time'])->format('l, F j, Y h:i A'),
//     Thursday, May 30, 2024 - 08:35
      "Length" => "{$this->bookingDetails['duration']} minutes",
      "Audience Age" => $this->bookingDetails['Booking_Information.Youth_or_Adult'],
      "Presenter(s)" => implode(', ', $this->bookingDetails['assignee_contacts']),
    ];

    $markup = [];

    foreach ($details as $label => $value) {
      $markup[] = "<div class='webform-flexbox'><dt>{$label}</dt><dd>{$value}</dd></div>";
    }

    $markup = implode("\n", $markup);
    $markup = "<div class='sace-feedback-summary-form--booking-details-intro'>{$markup}</div>";

    $form['booking_details'] = [
      '#type' => 'markup',
      '#markup' => $markup,
    ];
  }

  protected function addFeedbackCounts(array &$form) {
    $form['feedback_counts'] = [
      '#type' => 'webform_flexbox',
    ];

    $submittedForms = \Civi\Api4\Activity::get(FALSE)
      ->addSelect('source_contact_id')
      ->addWhere('Feedback_Form.Booking', '=', $this->bookingId)
      ->addWhere('activity_type_id:name', '!=', 'Feedback Summary')
      ->execute()
      ->column('source_contact_id');

    $submittedOnline = count(array_filter($submittedForms, fn ($sourceContactId) => ($sourceContactId === self::UNASSIGNED_USER_CONTACT_ID)));

    $form['feedback_counts']['number_participants'] = [
      '#title' => 'Number of participants',
      '#type' => 'number',
      '#default_value' => $this->bookingDetails['Booking_Information.Number_of_Participants_per_course'],
          '#flex' => 1,
          '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
          '#suffix' => '</div></div>',
    ];
    $form['feedback_counts']['submitted_online'] = [
      '#title' => 'Number of online evaluations',
      '#type' => 'number',
      '#default_value' => $submittedOnline,
          '#flex' => 1,
          '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
          '#suffix' => '</div></div>',
    ];
    $form['feedback_counts']['submitted_by_staff'] = [
      '#title' => 'Number of staff entered evaluations',
      '#type' => 'number',
      '#default_value' => count($submittedForms) - $submittedOnline,
          '#flex' => 1,
          '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
          '#suffix' => '</div></div>',
    ];

  }

  protected function addSummaryFields(array &$form) {
    $summaryFieldGroups = $this->getSummaryFields();

    foreach ($summaryFieldGroups as $sourceField => $groupFields) {

      $groupElementKey = 'summary_group_' . str_replace('.', '_', $sourceField);

      $form[$groupElementKey] = [
        '#type' => 'fieldset',
      ];

      foreach ($groupFields as $key => $field) {
        $form[$groupElementKey][$key] = $field;
      }
    }
  }

  /**
   * Fetch info about what fields appear on the feedback form for this activity
   * And from this determine the corresponding summary fields
   * @param int $bookingId
   * @return array
   */
  protected function getSummaryFields(): array
  {
    // TODO cache by booking id more persistently
    if (!$this->summaryFields) {
      $feedbackFields = Utils::getFeedbackCustomFieldsForBooking($this->bookingId);

      $summaryFields = [];

      foreach ($feedbackFields as $feedbackField) {
        // api4 key for the field
        $fieldKey = "{$feedbackField['custom_group_id.name']}.{$feedbackField['name']}";
        $feedbackField['field_key'] = $fieldKey;

        // if options add options total fields
        if ($feedbackField['option_group_id']) {
          $summaryFields[$fieldKey] = $this->getSummaryFieldsForOptionQuestion($feedbackField);
        }
        else {
          // TODO: other field types?
          $summaryFields[$fieldKey] = $this->getSummaryFieldsForTextQuestion($feedbackField);
        }
      }
      $this->summaryFields = $summaryFields;
    }

    return $this->summaryFields;
  }

  private function getSummaryFieldsForTextQuestion(array $field): array {
    $elementPrefix = 'summary_' . str_replace('.', '_', $field['field_key']);

    if (strlen($elementPrefix) > 200) {
      $elementPrefix = \substr($elementPrefix, 0, 180) . \substr(\md5($elementPrefix), 0, 20);
    }

    $responses = $this->getPrepopulateValue($field['field_key'], 'response_list');

    $summaryFields = [
      "{$elementPrefix}_title" => [
        '#type' => 'markup',
        '#markup' => "<strong>{$field['label']}</strong>",
      ],
      "{$elementPrefix}_list" => [
        '#type' => 'markup',
        '#markup' => $responses['markup'],
      ],
      "{$elementPrefix}_summary" => [
        "#type" => 'webform_flexbox',
        "{$elementPrefix}_count" => [
          '#type' => 'number',
          '#title' => 'Number of responses',
          '#default_value' => $responses['count'],
          '#flex' => 1,
          '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
          '#suffix' => '</div></div>',
        ],
        "{$elementPrefix}_sentiment" => [
          '#type' => 'textfield',
          '#title' => 'Overall sentiment',
          '#flex' => 1,
          '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
          '#suffix' => '</div></div>',
        ],
      ],
      "{$elementPrefix}_notable" => [
        '#type' => 'textarea',
        '#title' => 'Notable responses',
        //'summary_type' => 'notable',
      ],
    ];

    return $summaryFields;
  }


  private function getSummaryFieldsForOptionQuestion(array $field): array {
    $elementPrefix = 'summary_' . str_replace('.', '_', $field['field_key']);

    if (strlen($elementPrefix) > 150) {
      $elementPrefix = \substr($elementPrefix, 0, 140) . \substr(\md5($elementPrefix), 0, 10);
    }

    $summaryFields = [
      "{$elementPrefix}_title" => [
        '#type' => 'markup',
        '#markup' => "<strong>{$field['label']}</strong>",
      ],
      "{$elementPrefix}_options" => [
        '#type' => 'webform_flexbox',
      ],
    ];

    $options = \Civi\Api4\OptionValue::get(FALSE)
      ->addWhere('option_group_id', '=', $field['option_group_id'])
      ->execute();

    $totalCount = 0;

    foreach ($options as $option) {
      $elementKey = "{$elementPrefix}_option_{$option['name']}";
      if (\strlen($elementKey) > 200) {
        $elementKey = \substr($elementKey, 0, 190) . \substr(\md5($elementKey), 0, 10);
      }
      $summaryFields["{$elementPrefix}_options"][$elementKey] = [
        '#type' => 'number',
        '#title' => "# {$option['label']}",
        '#title_display' => 'invisible',
        '#description' => "{$option['label']}",
        '#default_value' => $this->getPrepopulateValue($field['field_key'], 'option_total', $option['value']),
        '#flex' => 1,
        '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
        '#suffix' => '</div></div>',
      ];
      $totalCount += $summaryFields["{$elementPrefix}_options"][$elementKey]['#default_value'];
    }

//    $grandTotalTemplate = implode(' + ', $fieldsForGrandTotalTemplate);
 //   $grandTotalTemplate = "{{ {$grandTotalTemplate} }}";

    $summaryFields["{$elementPrefix}_options"]["{$elementPrefix}_total_all_options"] = [
      '#type' => 'markup',
      '#markup' => "
        <div class='form-item sace-feedback-forms-option-total'>
          <strong class='form-item__label'>Total</strong>
          <span>{$totalCount}</span>
        </div>
      ",
      '#flex' => 1,
      '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
      '#suffix' => '</div></div>',
    ];

    return $summaryFields;
  }

  private function getPrepopulateValue($sourceField, $summaryType, $optionValue = NULL) {
    switch ($summaryType) {
      case 'option_total':
        return \Civi\Api4\Activity::get(FALSE)
          ->addWhere('Feedback_Form.Booking', '=', $this->bookingId)
          ->addWhere($sourceField, '=', $optionValue)
          ->addSelect('row_count')
          ->execute()
          ->count();

      case 'response_list':
        $responses = \Civi\Api4\Activity::get(FALSE)
          ->addWhere('Feedback_Form.Booking', '=', $this->bookingId)
          ->addWhere($sourceField, 'IS NOT EMPTY')
          ->addSelect($sourceField)
          ->execute()
          ->column($sourceField);

        $responses = array_filter($responses);

        if (!$responses) {
          return [
            'count' => 0,
            'markup' => '<div>No responses</div>',
          ];
        }

        $list = array_map(fn($r) => sprintf('<li>%s</li>', $r), $responses);
        $list = implode(' ', $list);
        $markup = <<<HTML
            <details class="package-listing js-form-wrapper form-wrapper claro-details claro-details--package-listing">
              <summary role="button" aria-controls="edit-modules-core" aria-expanded="false" aria-pressed="false" class="claro-details__summary claro-details__summary--package-listing">
                See responses<span class="claro-details__summary-summary"></span>
              </summary>
              <div class="claro-details__wrapper details-wrapper claro-details__wrapper--package-listing">
                <ul>{$list}</ul>
              </div>
            </details>
          HTML;

        return [
          'count' => count($responses),
          'markup' => $markup,
        ];
    }
  }

  protected function replaceTokens(&$form) {
    $tokenValues = [
      '[the presentation topic]' => $this->bookingDetails['topic'],
    ];
    TokenReplacement::run($tokenValues, $form);
  }

  protected function addVerifiedByField(&$form) {
//    $loggedInContactId = \CRM_Core_Session::getLoggedInContactID();
//    $currentContact = \Drupal::entityTypeManager()->getStorage('civicrm_contact')->load($loggedInContactId);
//    // source contact = logged in
//    $form['verified_by'] = [
//      '#title' => 'Verified By',
//      '#type' => 'entity_autocomplete',
//      '#target_type' => 'civicrm_contact',
//      '#default_value' => $currentContact,
//      '#readonly' => TRUE,
//    ];
    $loggedInContactId = \CRM_Core_Session::getLoggedInContactID();
    $currentContact = \Civi\Api4\Contact::get(FALSE)->addWhere('id', '=', $loggedInContactId)->addSelect('display_name')->execute()->single();
    $name = $currentContact['display_name'];
    $form['verified_by'] = [
      '#type' => 'markup',
      '#markup' => "<div class='webform-flexbox'>
        <strong>Verified By</strong><span>{$name}</span>
      </div>",
    ];
  }

  /**
   * Handle submission and saving eval data
   */
  private function getOrCreateSummaryDataField(string $summaryFieldKey): string {
    $summaryFieldKey = str_replace('summary_', '', $summaryFieldKey);

    $label = \str_replace('_', ' ', $summaryFieldKey);

    if (\strlen($summaryFieldKey) >= 64) {
      $parts = explode('_option_', $summaryFieldKey);
      $partLength = 64 / count($parts);
      foreach ($parts as $part) {
        \substr($summaryFieldKey, 0, 54) . \substr(md5($summaryFieldKey), 10);

      }

    }
    // TODO: might summary field key be too long for custom field name column?
    $existingField = \Civi\Api4\CustomField::get(FALSE)
      // should we restrict to a particular custom field group?
      // probably the key is specific enough
      //->addWhere('custom_group_id.name', '=', 'Feedback_Summary')
      ->addWhere('name', '=', $summaryFieldKey)
      ->addSelect('name', 'custom_group_id.name')
      ->execute()
      ->first();

    if ($existingField) {
      return $existingField['custom_group_id.name'] . '.' . $existingField['name'];
    }

    \Civi\Api4\CustomField::create(FALSE)
      ->addValue('label', $label)
      ->addValue('name', $summaryFieldKey)
      ->addValue('custom_group_id.name', 'Feedback_Summary')
      ->addValue('html_type', 'Text')
      ->execute()
      ->first();

    return 'Feedback_Summary.' . $summaryFieldKey;
  }

  /**
   * Save submitted values to CiviCRM activity
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $formValues = $form_state->getValues();

    // TODO: what if we want to update an existing summary?
    $saveSummary = \Civi\Api4\Activity::create(FALSE)
      ->addValue('activty_type_id:name', 'Feedback Summary')
      ->addValue('Feedback_Form.Booking', $this->bookingId);
      //->addValue('source_contact_id', $formValues['source_contact']);

    // TODO: add other standard fields for contacts / verified by etc
    foreach ($formValues as $key => $value) {
      if (\str_starts_with($key, 'summary_') && !\is_null($value)) {
        $storageField = $this->getOrCreateSummaryDataField($key);
        $saveSummary->addValue($storageField, $value);
      }
    }

    $summaryId = $saveSummary->execute()->first()['id'];

//    $activityContactSave = \Civi\Api4\ActivityContact::save(FALSE);

//    foreach ($formValues['target_contacts'] as $contactId) {
//      $activityContactSave->addRecord([
//        'contact_id' => $contactId,
//        'activity_id' => $summaryId,
//        'record_type_id:name' => 'target',
//      ]);
//    }

//    foreach ($formValues['assinee_contacts'] as $contactId) {
//      $activityContactSave->addRecord([
//        'contact_id' => $contactId,
//        'activity_id' => $summaryId,
//        'record_type_id:name' => 'assignee',
//      ]);
//    }

//    $activityContactSave->execute();
  }

}
