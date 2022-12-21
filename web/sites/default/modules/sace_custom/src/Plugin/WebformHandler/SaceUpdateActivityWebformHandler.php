<?php

namespace Drupal\sace_custom\Plugin\WebformHandler;

use Civi\Api4\Activity;
use Civi\Api4\ActivityContact;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace CiviCRM Activity Update Handler.
 *
 * @WebformHandler(
 *   id = "sace_activity_update",
 *   label = @Translation("Sace Activity Update"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Handle Updating Acivity appropriately."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class SaceUpdateActivityWebformHandler extends WebformHandlerBase {

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

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $this->database = \Drupal::database();
    $webform_submission_data = $webform_submission->getData();
    $civicrm_submission_data = $this->database->query("SELECT civicrm_data FROM {webform_civicrm_submissions} WHERE sid = :sid", [
      ':sid' => $webform_submission->id(),
    ]);
\CRM_Core_Error::debug_var('a', $civicrm_submission_data);

    if ($civicrm_submission_data && !empty($webform_submission_data['rescheduled_activity_date'])) {
      while ($row = $civicrm_submission_data->fetchAssoc()) {
        $data = unserialize($row['civicrm_data']);
        $activity = Activity::get(FALSE)->addWhere('id', '=', $data['activity'][1]['id'])->execute()[0];
        $activityContacts = ActivityContact::get(FALSE)->addWhere('activity_id', '=', $data['activity'][1]['id'])->execute();
        unset($activity['id']);
        $activity['activity_date_time'] = $webform_submission_data['rescheduled_activity_date'];
        $activity['duration'] = $webform_submission_data['recheduled_activity_duration'];
	$activity['status_id'] = \CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'status_id', 'Scheduled');
	$activity['source_contact_id'] = $webform_submission_data['civicrm_1_contact_1_contact_existing'];
        $newActivity = Activity::create(FALSE)->setValues($activity)->execute();
	foreach ($activityContacts as $activityContact) {
          ActivityContact::create(FALSE)->addValue('activity_id', $newActivity[0]['id'])->addValue('contact_id', $activityContact['contact_id'])->addValue('record_type_id', $activityContact['record_type_id'])->execute();
        }
      }
    }
  }

}
