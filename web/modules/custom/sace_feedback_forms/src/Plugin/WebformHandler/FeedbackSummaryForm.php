<?php

namespace Drupal\sace_feedback_forms\Plugin\WebformHandler;

use Drupal\sace_feedback_forms\Utils;
use Drupal\webform\Utility\WebformFormHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace Feedback Form Handler

 * @WebformHandler(
 *   id = "sace_feedback_summary_form_handler",
 *   label = @Translation("SACE Feedback Summary Form Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler used for producing feedback summaries"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class FeedbackSummaryForm extends WebformHandlerBase
{
  /**
   * The CiviCRM service.
   *
   * @var \Drupal\civicrm\Civicrm
   */
  protected $civicrm;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  protected int $bookingId;

  protected array $summaryFields = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->civicrm = $container->get('civicrm');
    $instance->database = \Drupal::database();
    return $instance;
  }

  protected function fetchBookingId(): void
  {
    $this->bookingId = (int) \Drupal::request()->query->get('bid') ?? 0;

    if (!$this->bookingId) {
      \Drupal::messenger()->addWarning('Summary webform expects a booking id to be passed in the bid parameter');
      //throw new \CRM_Core_Error('Booking must be identified with bid parameter');
    }
  }

  public function alterElements(array &$elements, WebformInterface $webform)
  {
    $this->civicrm->initialize();
    $this->fetchBookingId();

    $elements['booking_id'] ??= [
      '#type' => 'hidden',
      '#default_value' => $this->bookingId,
    ];
    $elements['booking_id_display'] ??= [
      '#type' => 'number',
      '#default_value' => $this->bookingId,
      '#readonly' => TRUE,
    ];

    $summaryFieldGroups = $this->getSummaryFields();

    foreach ($summaryFieldGroups as $sourceField => $groupFields) {

      $groupElementKey = 'summary_group_' . str_replace('.', '_', $sourceField);

      $elements[$groupElementKey] ??= [
        '#type' => 'fieldset',
      ];

      foreach ($groupFields as $key => $field) {
        $elements[$groupElementKey][$key] ??= $field;
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
        } else {
          // TODO: other field types?
          $summaryFields[$fieldKey] = $this->getSummaryFieldsForTextQuestion($feedbackField);
        }
      }
      $this->summaryFields = $summaryFields;
    }

    return $this->summaryFields;
  }

  private function getSummaryFieldsForTextQuestion(array $field): array
  {
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
      "{$elementPrefix}_count" => [
        '#type' => 'number',
        '#title' => 'Number of responses',
        '#default_value' => $responses['count'],
      ],
      "{$elementPrefix}_sentiment" => [
        '#type' => 'textfield',
        '#title' => 'Overall sentiment',
        'summary_type' => 'sentiment',
      ],
      "{$elementPrefix}_notable" => [
        '#type' => 'textarea',
        '#title' => 'Notable responses',
        'summary_type' => 'notable',
      ],
    ];

    return $summaryFields;
  }


  private function getSummaryFieldsForOptionQuestion(array $field): array
  {
    $elementPrefix = 'summary_' . str_replace('.', '_', $field['field_key']);

    if (strlen($elementPrefix) > 200) {
      $elementPrefix = \substr($elementPrefix, 0, 190) . \substr(\md5($elementPrefix), 0, 10);
    }

    $summaryFields = [
      "{$elementPrefix}_title" => [
        '#type' => 'markup',
        '#markup' => "<strong>{$field['label']}</strong>",
      ],
      "{$elementPrefix}_options" => [
        '#type' => 'webform_flexbox',
        //'#attributes' => ['class' => ['webform-flexbox']],
      ],
    ];

    $options = \Civi\Api4\OptionValue::get(FALSE)
      ->addWhere('option_group_id', '=', $field['option_group_id'])
      ->execute();

    $fieldsForGrandTotalTemplate = [];

    foreach ($options as $option) {
      $elementKey = "{$elementPrefix}_option_{$option['name']}";
      if (\strlen($elementKey) > 250) {
        $elementKey = \substr($elementKey, 0, 240) . \substr(\md5($elementKey), 0, 10);
      }
      $summaryFields["{$elementPrefix}_options"][$elementKey] = [
        '#type' => 'number',
        '#title' => "# {$option['label']}",
        '#title_display' => 'invisible',
        '#description' => "{$option['label']}",
        '#field_prefix' => '#',
        '#default_value' => $this->getPrepopulateValue($field['field_key'], 'option_total', $option['value']),
        '#webform_parent_flexbox' => TRUE,
      ];
      $fieldsForGrandTotalTemplate[] = "data.{$elementKey}|default(0)";
    }

    $grandTotalTemplate = implode(' + ', $fieldsForGrandTotalTemplate);
    $grandTotalTemplate = "{{ {$grandTotalTemplate} }}";

    $summaryFields["{$elementPrefix}_options"]["{$elementPrefix}_total_all_options"] = [
      '#type' => 'webform_computed_twig',
      '#title' => 'Total',
      'summary_type' => 'grand_total',
      '#template' => $grandTotalTemplate,
      '#ajax' => TRUE,
      '#data_type' => 'Float',
      '#webform_parent_flexbox' => TRUE,
    ];

    return $summaryFields;
  }

  /**
   * Prepoluates summary form field values based on feedback submissions in the database
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function alterForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission)
  {
    // Get activity id from query var.
    $this->fetchBookingId();
    \Drupal::service('civicrm')->initialize();

    // Get webform form elements.
    $elements = WebformFormHelper::flattenElements($form);

    $this->prepopulateSummaryHeader($elements);
  }

  protected function prepopulateSummaryHeader(&$elements)
  {
    $activityContacts = (array) \Civi\Api4\ActivityContact::get(FALSE)
      ->addWhere('activity_id', '=', $this->bookingId)
      ->addSelect('contact_id', 'record_type_id:name')
      ->execute();

    $targets = array_filter($activityContacts, fn($record) => ($record['record_type_id:name'] === 'target'));
    $assignees = array_filter($activityContacts, fn($record) => ($record['record_type_id:name'] === 'assignee'));

    // targets => webform contact 2
    $elements['civicrm_2_contact_1_contact_existing']['#value'] = array_map(fn($record) => $record['contact_id'], $targets);

    // logged in contact => webform contact 3
    $elements['civicrm_3_contact_1_contact_existing']['#default_value'] = \CRM_Core_Session::getLoggedInContactID();

    // assignees => webform contacts 4,5,...
    $i = 4;
    foreach ($assignees as $activityContact) {
      $elements["civicrm_{$i}_contact_1_contact_existing"]['#value'] = $activityContact['contact_id'];
      $i++;
    }

    // Get Booking Intake custom fields
    $result = \Civi\Api4\Activity::get(FALSE)
      //    ->addSelect( 'Booking_Information.Presentation_topics', 'Booking_Information.Presentation_topics:label', 'Booking_Information.Presentation_custom')
      //    ->addSelect('Booking_Information.Number_of_Participants_per_course', 'activity_type_id', 'Booking_Information.Online_Courses', 'Booking_Information.Privilege_and_Oppression_Content', 'Booking_Information.Resources_Content', 'Booking_Information.Support_Content', 'Booking_Information.Presentation_topics', 'Booking_Information.Presentation_topics:label', 'Booking_Information.Safer_Spaces_Content', 'Booking_Information.Facilitating_Program', 'Booking_Information.Presentation_Method', 'Booking_Information.Presentation_custom', 'Booking_Information.Audience')
      ->addSelect('activity_type_id', 'activity_date_time', 'Booking_Information.Youth_or_Adult', 'Booking_Information.Online_Courses', 'activity_contact.contact_id', 'Booking_Information.Privilege_and_Oppression_Content', 'Booking_Information.Resources_Content', 'Booking_Information.Support_Content', 'Booking_Information.Presentation_topics', 'Booking_Information.Presentation_topics:label', 'Booking_Information.Safer_Spaces_Content', 'Booking_Information.Facilitating_Program', 'Booking_Information.Presentation_Method', 'Booking_Information.Presentation_custom', 'Booking_Information.Audience', 'Booking_Information.Facilitating_Program', 'Booking_Information.Number_of_Participants_per_course')
      ->addJoin('ActivityContact AS activity_contact', 'INNER')
      ->addGroupBy('id')
      ->addWhere('activity_contact.record_type_id', '=', 3)
      ->addWhere('id', '=', $this->bookingId)
      ->execute()
      ->first();

    if (!empty($result['Booking_Information.Online_Courses'])) {
      $topic = \Civi\Api4\OptionValue::get(FALSE)
        ->addSelect('description')
        ->addWhere('value', 'LIKE', $result['Booking_Information.Online_Courses'])
        ->execute()->first()['description'];;
    } else {
      $topics = (array) $result['Booking_Information.Presentation_topics:label'] ?? [];
      $topic = implode(', ', $topics);
      if (in_array('Custom / Unsure', $topics)) {
        $topic = str_replace('Custom / Unsure', $result['Booking_Information.Presentation_custom'], $topic);
      }
    }

    // replace tokens in markup text
    if (!$topic) {
      $topic = 'the topics covered';
    }

    foreach ($elements as &$element) {
      if (isset($element['#text'])) {
        $element['#text'] = str_replace('[the presentation topic]', $topic, $element['#text']);
      } elseif (isset($element['#markup'])) {
        $element['#markup'] = str_replace('[the presentation topic]', $topic, $element['#markup']);
      }
    }

    // Get student feedback from webform submitted data.
    $feedback = $result['Booking_Information.Number_of_Participants_per_course'];

    // TODO: what are the contact IDs and activity type IDs here?
    $onlineCount = \Civi\Api4\ActivityContact::get(FALSE)
      ->addJoin('Activity AS activity', 'INNER', ['activity.id', '=', 'activity_id'])
      ->addWhere('activity.PED_Booking_Reference.Booking_Reference_ID', '=', $this->bookingId)
      ->addWhere('record_type_id', '=', 2)
      ->addWhere('contact_id', '=', 860)
      ->addWhere('activity.activity_type_id', '=', 197)
      ->execute()
      ->count();

    $totalCount = \Civi\Api4\ActivityContact::get(FALSE)
      ->addJoin('Activity AS activity', 'INNER', ['activity.id', '=', 'activity_id'])
      ->addWhere('activity.PED_Booking_Reference.Booking_Reference_ID', '=', $this->bookingId)
      ->addWhere('record_type_id', '=', 2)
      ->addWhere('activity.activity_type_id', '=', 197)
      ->execute()
      ->count();

    $elements['civicrm_1_activity_1_cg54_custom_458']['#default_value'] = $onlineCount;
    $elements['civicrm_1_activity_1_cg54_custom_459']['#default_value'] = $totalCount - $onlineCount;

    // # of returned evaluations
    $elements['civicrm_1_activity_1_cg54_custom_24']['#default_value'] = $feedback;
  }

  private function getPrepopulateValue($sourceField, $summaryType, $optionValue = NULL)
  {
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
            'markup' => 'No responses',
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


  private function getOrCreateSummaryDataField(string $summaryFieldKey): string
  {
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
      ->addValue('label', $summaryFieldKey)
      ->addValue('name', $summaryFieldKey)
      ->addValue('custom_group_id.name', 'Feedback_Summary')
      ->addValue('html_type', 'Text')
      ->execute()
      ->first();

    return 'Feedback_Summary.' . $summaryFieldKey;
  }

  /**
   * Not sure why this is necessary but not data for fields added with hooks seems
   * to get lost otherwise
   */
  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $webformData = $webform_submission->getData();

    $formValues = $form_state->getValues();
    $this->bookingId = $formValues['booking_id'];
    $webformData['booking_id'] = $this->bookingId;

    $summaryFields = WebformFormHelper::flattenElements($this->getSummaryFields());
    foreach ($summaryFields as $key => $info) {
      if (isset($formValues[$key]) && !isset($webformData[$key])) {
        $webformData[$key] = $formValues[$key];
      }
    }

    $webform_submission->setData($webformData);
  }

  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE)
  {
    $webform_submission_data = $webform_submission->getData();

    // TODO: what if we want to update an existing summary?
    $saveSummary = \Civi\Api4\Activity::create(FALSE)
      ->addValue('activty_type_id:name', 'Feedback Summary')
      ->addValue('Feedback_Form.Booking', $this->bookingId);

    // TODO: add other standard fields for contacts / verified by etc
    foreach ($webform_submission_data as $webformKey => $value) {
      if (\str_starts_with($webformKey, 'summary_') && !\is_null($value)) {
        $storageField = $this->getOrCreateSummaryDataField($webformKey);
        $saveSummary->addValue($storageField, $value);
      }
    }

    $saveSummary->execute();
  }
}
