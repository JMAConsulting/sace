<?php

use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace CiviCRM Activity Update Handler.
 *
 * @WebformHandler(
 *   id = "multiple_booking_sessions",
 *   label = @Translation("Multiple Booking Sessions handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Handle Creating multple bookings from one activity"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class MultipleBookingSessionsWebformHandler extends WebformHandlerBase {

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
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $webform_submission_data = $webform_submission->getData();
    $civicrm_submission_data = $this->database->query("SELECT civicrm_data FROM {webform_civicrm_submissions} WHERE sid = :sid", [
      ':sid' => $webform_submission->id(),
    ]);
    if ($civicrm_submission_data) {
      while ($row = $civicrm_submission_data->fetchAssoc()) {
        $civicrm_data = unserialize($row['civicrm_data']);
        $activity = $this->api('Activity', 'get', ['id' => $civicrm_data['activity'][1]['id']])['values'][$civicrm_data['activity'][1]['id']];
        $activityContacts = $this->api('ActivityContact', 'get', ['activity_id' => $civicrm_data['activity'][1]['id']])['values'];
        for ($key = 1; $key <= 10; $key++) {
          if (!empty($webform_submission['followup_activity_date_' . $key])) {
            $newActivity = $activity;
            unset($newActivity[id]);
            $newActivity['activity_date_time'] = $webform_submission['additional_appointment_' . $key];
            $newActivityRecord = $this->api('Activity', 'create', $newActivity);
            foreach ($activityContacts as $contact) {
              $this->api('ActivityContact', 'create', [
                'activity_id' => $newActivityRecord['id'],
                'contact_id' => $contact['contact_id'],
                'record_type_id' => $contact['record_type_id'],
              ]);
            }
          }
        }
      }
    }
  }

  protected function api(string $entity, string $action, $params = []) {
    return \civicrm_api3($entity, $action, $params);
  }

}
