<?php

namespace Drupal\multiplebookingsessions\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Civi\Api4\Activity;
use Civi\Api4\ActivityRole;

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
    if ($webform_submission_data) {
      $activity = Activity::get(FALSE)
        ->addSelect('*')
        ->addSelect('custom.*')
        ->addWhere('id', '=', $webform_submission_data['activity_id'])
        ->execute()
        ->first();
      $activityContacts = $this->api('ActivityContact', 'get', ['activity_id' => $webform_submission_data['activity_id'], 'record_type_id' => ['!=' => 'Activity Source']])['values'];
      $source_contact_id = $this->api('ActivityContact', 'get', ['activity_id' => $webform_submission_data['activity_id'], 'record_type_id' => "Activity Source", 'sequential' => 1])['values'][0]['contact_id'];
      for ($key = 1; $key <= 10; $key++) {
        if (!empty($webform_submission_data['additional_appointment_' . $key])) {
          $newActivity = $activity;
          $newActivity['source_contact_id'] = $source_contact_id;
          unset($newActivity['id']);
          $newActivity['activity_date_time'] = $webform_submission_data['additional_appointment_' . $key];
          // Update End date custom field based on new start date and duration.
          $newActivity['Booking_Information.End_Date'] = date('Y-m-d H:i:s', strtotime('+' . $activity['duration'] . 'minutes', strtotime($newActivity['activity_date_time'])));
          $newActivityRecord = Activity::create(FALSE)
            ->setValues($newActivity)
            ->execute()
            ->first();
          foreach ($activityContacts as $contact) {
            $this->api('ActivityContact', 'create', [
              'activity_id' => $newActivityRecord['id'],
              'contact_id' => $contact['contact_id'],
              'record_type_id' => $contact['record_type_id'],
            ]);
            // Copy across activity roles as well.
            $activityRole = ActivityRole::get(FALSE)
              ->addWhere('assignee_contact_id', '=', $contact['contact_id'])
              ->addWhere('activity_id', '=', $activity['id'])
              ->execute()->first();
            if (!empty($activityRole)) {
              ActivityRole::create(FALSE)
                ->setValues([
                  'activity_id' => $newActivityRecord['id'],
                  'assignee_contact_id' => $contact['contact_id'],
                  'role_id' => $activityRole['role_id'],
                ])
              ->execute();
            }
          }
        }
      }
    }
  }

  public function alterForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $request = \Drupal::request();
    // Support legacy url param
    if (empty($request->query->get('activity1')) && !empty($request->query->get('aid'))) {
      $request->query->set('activity1', $request->query->get('aid'));
    }
    if (!empty($request->query->get('activity1')) && empty($form['elements']['activity_id']['#value'])) {
      $form['elements']['activity_id']['#value'] = $request->query->get('activity1');
    }
  }

  protected function api(string $entity, string $action, $params = []) {
    return \civicrm_api3($entity, $action, $params);
  }

}
