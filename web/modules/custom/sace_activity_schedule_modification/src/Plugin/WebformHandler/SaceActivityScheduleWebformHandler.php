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
        $team_contact_ids = UFMatch::get(FALSE)->addWhere('uf_id', 'IN', $user_ids)->execute()->column('contact_id');
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
          \Civi\Api4\ActivityContact::delete()->addWhere('id', '=', $activityContact['id'])->execute();
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
      }
    }
  }

}
