<?php

namespace Drupal\clin_book_appointment\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace CiviCRM Activity Book Handler.
 *
 * @WebformHandler(
 *   id = "clin_book_appointment_handler",
 *   label = @Translation("CLIN Book Appointment Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler for fixing the CLIN Book Appointment Form"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class ClinBookAppointmentWebformHandler extends WebformHandlerBase {

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
      // Do not create a new activty if appointment status is attended, scheduled, or completed
      if(($webform_submission_data['civicrm_1_activity_1_activity_status_id'] != 10 && $webform_submission_data['civicrm_1_activity_1_activity_status_id'] != 2
      && $webform_submission_data['civicrm_1_activity_1_activity_status_id'] != 1) || $webform_submission_data['book_an_appointment'] == 1) {

        // Schedule reminder one week later
        if($webform_submission_data['civicrm_1_activity_1_activity_status_id'] != 14 && $webform_submission_data['civicrm_1_activity_1_activity_status_id'] != 15 && $webform_submission_data['book_an_appointment'] != 1) {
          $results = \Civi\Api4\Activity::create(FALSE)
            ->addValue('parent_id', $webform_submission_data['aid']) // Original appointment ID
            ->addValue('source_contact_id', $webform_submission_data['civicrm_1_contact_1_contact_existing'])
            ->addValue('activity_type_id', 346) // CLIN - Reminder activity type
            ->addValue('activity_date_time', date('Y-m-d H:i:s', strtotime('+1 week', time())))
            ->addValue('duration', $webform_submission_data['civicrm_1_activity_1_activity_duration'])
            ->addValue('status_id', 1) // Set reminder status to scheduled
            ->addValue('target_contact_id', $webform_submission_data['civicrm_2_contact_1_contact_existing'])
            ->addValue('assignee_contact_id', $webform_submission_data['civicrm_1_contact_1_contact_existing']) // Assign to receptionist
            ->addValue('activity_details', 	$webform_submission_data['civicrm_1_activity_1_activity_details'])
            ->execute();
        }
        // Schedule updated appointment
        else {
          $results = \Civi\Api4\Activity::create(FALSE)
            ->addValue('source_contact_id', $webform_submission_data['civicrm_1_contact_1_contact_existing'])
            ->addValue('activity_type_id', $webform_submission_data['civicrm_1_activity_1_activity_activity_type_id'])
            ->addValue('activity_date_time', $webform_submission_data['activity_date_time'])
            ->addValue('duration', $webform_submission_data['civicrm_1_activity_1_activity_duration'])
            ->addValue('status_id', $webform_submission_data['civicrm_1_activity_1_activity_status_id'])
            ->addValue('target_contact_id', $webform_submission_data['civicrm_2_contact_1_contact_existing'])
            ->addValue('assignee_contact_id', $webform_submission_data['civicrm_3_contact_1_contact_existing'])
            ->addValue('activity_details', 	$webform_submission_data['civicrm_1_activity_1_activity_details'])
            ->execute();
        }
      }
      if($webform_submission_data['book_an_appointment'] != '') {
        $results = \Civi\Api4\Activity::update(TRUE)
          ->addValue('status_id', 2)
          ->addValue('activity_type_id', 346) // CLIN - Reminder activity type
          ->addWhere('id', '=', $webform_submission_data['aid'])
          ->execute();
      } 
    }
  }
}


