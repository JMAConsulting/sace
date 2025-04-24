<?php
namespace Drupal\sace_activity_schedule_modification\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Civi\Api4\Activity;
use Civi\Api4\ActivityContact;
use Civi\Api4\UFMatch;

/**
 * Sace Activity Schedule Webform Handler plugin.
 *
 * @WebformHandler(
 *   id = "sace_activity_schedule",
 *   label = @Translation("Sace Activity Schedule"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Sace Activity Schedule Handler"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class SaceActivityScheduleWebformHandler extends WebformHandlerBase {

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

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->civicrm = $container->get('civicrm');
    $instance->database = \Drupal::database();
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->validateDateDuration($form_state);
  }

  /**
   * Validate date duration to ensure end date is not before the start date.
   */
  private function validateDateDuration(FormStateInterface $formState) {
    $duration = (int) $formState->getValue('civicrm_1_activity_1_activity_duration') ?? NULL;
    $startDate = $formState->getValue('civicrm_1_activity_1_activity_activity_date_time');
    $endDate = $formState->getValue('civicrm_1_activity_1_cg2_custom_661');
    // if someone manually update the duration to be positive as it is editable, we are using timediff first to ensure start date is always before the end date
    if ((strtotime($endDate) < strtotime($startDate))  || $duration < 0) {
      $formState->setErrorByName('civicrm_1_activity_1_cg2_custom_661', $this->t('Start Date cannot be after the End Date.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $webform_submission_data = $webform_submission->getData();
    $civicrm_submission_data = $this->database->query("SELECT civicrm_data FROM {webform_civicrm_submissions} WHERE sid = :sid", [
      ':sid' => $webform_submission->id(),
    ]);
    if ($webform_submission_data && $civicrm_submission_data) {
      while ($row = $civicrm_submission_data->fetchAssoc()) {
        $contacts = [];
        $data = unserialize($row['civicrm_data']);
        if (!empty($webform_submission_data['staff'])) {
          $contacts = array_merge($contacts, $webform_submission_data['staff']);
        }
        if (!empty($webform_submission_data['organisation_select'])) {
          $contacts = array_merge($contacts, $webform_submission_data['organisation_select']);
        }
        if (!empty($webform_submission_data['user_team'])) {
          $user_ids = array_keys(\Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['field_user_team' => $webform_submission_data['user_team']]));
          $team_contact_ids = UFMatch::get(FALSE)->addWhere('uf_id', 'IN', $user_ids)->addWhere('contact_id.contact_sub_type', '!=', 'Former_Staff')->execute()->column('contact_id');
          $contacts = array_merge($contacts, $team_contact_ids);
          $user_team = implode(',', $webform_submission_data['user_team']);
          Activity::update(FALSE)
            ->addWhere('id', '=', $data['activity'][1]['id'])
            ->addValue('CE_External_Activities.User_Team_filter', $user_team)
            ->execute();
        }
        if (!empty($contacts)) {
          $activityContacts = \Civi\Api4\ActivityContact::get(FALSE)
            ->addWhere('activity_id', '=', $data['activity'][1]['id'])
            ->addWhere('record_type_id:name', '=', 'Activity Assignees')
            ->execute();
          foreach ($activityContacts as $activityContact) {
            \Civi\Api4\ActivityContact::delete(FALSE)->addWhere('id', '=', $activityContact['id'])->execute();
          }
          $unique_contacts = array_unique($contacts);
          foreach ($unique_contacts as $contact_id) {
            ActivityContact::create(FALSE)
              ->addValue('contact_id', $contact_id)
              ->addValue('activity_id', $data['activity'][1]['id'])
              ->addValue('record_type_id:name', 'Activity Assignees')
              ->execute();
          }
        }
        if (!empty($webform_submission_data['repeat_enabled'])) {
          $this->saveRepeatingActivity($webform_submission_data, $data['activity'][1]['id']);
        }
      }
    }
  }

  /**
   * This is adapted from CRM_Core_Form_RecurringEntity::postProcess
   * to use our webform parameter names, and cut out logic that is specific
   * to CiviEvent
   *
   * @see CRM_Core_Form_RecurringEntity::postProcess
   */
  protected function saveRepeatingActivity($webformData, $initialActivityId) {
    \Civi::log()->debug("Saving activity repeat options for activity ID: {$initialActivityId}, webform data: " . json_encode($webformData));

    $actionScheduleId = $this->createActionSchedule($webformData, $initialActivityId);

    $recursion = new \CRM_Core_BAO_RecurringEntity();
    $recursion->scheduleId = $actionScheduleId;
    $recursion->dateColumns = ['activity_date_time'];

    if (!empty($webformData['repeat_exclude_dates'])) {
      $recursion->excludeDates = $webformData['repeat_exclude_dates'];
    }
    $recursion->entity_id = $initialActivityId;
    $recursion->entity_table = 'civicrm_activity';

    // ensure activity contacts are propagated to generated activities
    $recursion->linkedEntities = [
      [
        'table' => 'civicrm_activity_contact',
        'findCriteria' => [
          'activity_id' => $initialActivityId,
        ],
        'linkedColumns' => ['activity_id'],
        'isRecurringEntityRecord' => FALSE,
      ],
    ];

    $recursion->generate();

    //TODO: generate custom end date field values based on activity duration?
  }

  /**
   * Create the action schedule record for given params
   * @return int id of the created record
   */
  protected function createActionSchedule($webformData, $initialActivityId): int {

    $startDate = $webformData['repeat_start_date'];
    // if left blank will come through as empty date and time subfields
    // => default to main activity date
    if (!array_filter($startDate)) {
      $startDate = $webformData['civicrm_1_activity_1_activity_activity_date_time'];
    }

    $actionScheduleCreate = \Civi\Api4\ActionSchedule::create(FALSE)
      ->addValue('name', 'repeat_activity_' . $initialActivityId)
      ->addValue('title', 'Repetition Schedule for Activity ID ' . $initialActivityId)
      ->addValue('used_for', 'civicrm_activity')
      ->addValue('mapping_id:name', 'activity_type')
      ->addValue('entity_value', $initialActivityId)
      ->addValue('start_action_date', $startDate)
      ->addValue('start_action_unit', $webformData['repeat_interval_unit'])
      ->addValue('repetition_frequency_unit', $webformData['repeat_interval_unit'])
      ->addValue('repetition_frequency_interval', $webformData['repeat_interval_count']);

    // special "repeats on" config for week/month intervals
    switch ($webformData['repeat_interval_unit']) {

      case 'week':
        // civicrm expects lowercase weekday names
        $serialized = strtolower(implode(',', $webformData['repeat_days_of_week']));
        $actionScheduleCreate->addValue('start_action_condition', $serialized);
        break;

      case 'month':
        // for month interval, we have repeat on set date and repeat on regular weekday
        switch ($webformData['repeat_on_month_options']) {
          case 'date':
            $actionScheduleCreate->addValue('limit_to', $webformData['repeat_date_of_month']);
            break;

          case 'weekday':
            $actionScheduleCreate->addValue('entity_status', implode(' ', $webformData['repeat_weekday_of_month_ordinal'], $webformData['repeat_weekday_of_month_weekday']));
            break;
        }
        break;
    }

    switch ($webformData['repeat_end_type']) {
      case 'count':
        $actionScheduleCreate->addValue('start_action_offset', $webformData['repeat_end_after_count']);
        break;

      case 'date':
        $actionScheduleCreate->addValue('absolute_date', $webformData['repeat_end_date']);
        break;
    }

    // api4 requires one or other of these to be set
    // if we have neither (not using absolute date or weekly repetition)
    // then set a null start_action_condition
    if (!$actionScheduleCreate->getValue('absolute_date') && !$actionScheduleCreate->getValue('start_action_condition')) {
      $everyDay = 'monday,tuesday,wednesday,thursday,friday,saturday,sunday';
      $actionScheduleCreate->addValue('start_action_condition', $everyDay);
    }

    $result = $actionScheduleCreate->execute()->first();

    return $result['id'];
  }

}
