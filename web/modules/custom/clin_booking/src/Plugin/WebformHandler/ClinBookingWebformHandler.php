<?php

namespace Drupal\clin_booking\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Civi\Api4\EntityTag;

/**
 * Sace CiviCRM Activity Update Handler.
 *
 * @WebformHandler(
 *   id = "clin_booking_handler",
 *   label = @Translation("CLIN Booking Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler for fixing the CLIN Intake Booking Form"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class ClinBookingWebformHandler extends WebformHandlerBase {

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
   * Process webform submission when it is about to be saved. Called by the following hook:
   *
   * @see webform_civicrm_webform_submission_presave
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   */
  // public function preSave(WebformSubmissionInterface $webform_submission) {
  //   $webform_submission_data = $webform_submission->getData();
  //   if ($webform_submission_data && $webform_submission_data['are_you_the_legal_guardian'] === 'No' || $webform_submission_data['has_this_been_reported_'] === 'No') {
  //     $webform_submission_data['civicrm_1_activity_1_activity_activity_type_id'] = 336;
  //     $webform_submission->setData($webform_submission_data);
  //   }
  // }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $webform_submission_data = $webform_submission->getData();
  //   if ($webform_submission_data) {
  //     $activityParams = [
  //       'activity_type_id' => $webform_submission_data['civicrm_1_activity_1_activity_activity_type_id'], 
  //       'activity_date_time' => $webform_submission_data['civicrm_1_activity_1_activity_activity_date_time'],
  //     ];

  //     $dateTime = new DateTime($webform_submission_data['civicrm_1_activity_1_activity_activity_date_time']);
  //     $year = $dateTime->format('Y');

  //     $existingActivity = civicrm_api4('Activity', 'get', $activityParams);
  //     if ($existingActivity) {
  //       $activityParams['id'] = $existingActivity[0]['id'];
  //       $activityParams['CLIN_Adult_Intake_Activity_Data.Intake_Number'] = generateIntakeNumber($year);
  //     }

  //     $activityResult = civicrm_api4('Activity', 'update', $activityParams);
  //   }
   }
   // private function generateIntakeNumber($year) {
//   $activityParams = [
//     'activity_type_id' => $webform_submission_data['civicrm_1_activity_1_activity_activity_type_id'], 
//   ];
// }
}


