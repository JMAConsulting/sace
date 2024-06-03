<?php

namespace Drupal\clin_booking\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $webform_submission_data = $webform_submission->getData();

    if ($webform_submission_data) {
        $existingActivity = \Civi\Api4\Activity::get(FALSE)
        ->addWhere('activity_type_id', '=', $webform_submission_data['civicrm_1_activity_1_activity_activity_type_id'])
        ->addWhere('source_contact_id','=' ,'user_contact_id')
        ->addOrderBy('id', 'DESC')
        ->execute()
        ->first();

      if(empty($existingActivity)){
        $newActivity = \Civi\Api4\Activity::create(FALSE)
          ->addValue('activity_type_id', 336)
          ->addValue('activity_date_time', date('Y-m-d H:i:s', time()))
          ->addValue('subject','NEW')
          ->addValue('source_contact_id', 'user_contact_id')
          ->execute();
      }
      else {
        if($webform_submission_data && ((isset($webform_submission_data['has_this_been_reported_']) && $webform_submission_data['has_this_been_reported_'] === 'No') || (isset($webform_submission_data['proceed_with_booking_an_intake']) && $webform_submission_data['proceed_with_booking_an_intake'] === 'No'))) {
          \Civi\Api4\Activity::update(FALSE)
            ->addWhere('id', '=', $existingActivity['id'])
            ->addValue('activity_type_id', 336)
            ->execute();
        }
        else {
          $intakeNumber = $this->generateIntakeNumber($existingActivity);
          if($intakeNumber != NULL){
            \Civi\Api4\Activity::update(FALSE)
            ->addValue('CLIN_Adult_Intake_Activity_Data.Intake_Number', $intakeNumber)
            ->addWhere('id', '=', $existingActivity['id'])
            ->execute();
          }
        }
      }
    }
  }


  private function generateIntakeNumber($existingActivity)
  {
    // Get current year
    $year = date("Y");

    // Update intake number
    if($existingActivity['activity_type_id'] === 77)
      $prefix = 'A';
    else if ($existingActivity['activity_type_id'] === 78)
      $prefix = 'C';
    else
      return;

    $mostRecent = \Civi\Api4\Activity::get(FALSE)
      ->addSelect('CLIN_Adult_Intake_Activity_Data.Intake_Number')
      ->addWhere('CLIN_Adult_Intake_Activity_Data.Intake_Number', 'IS NOT NULL')
      ->addWhere('activity_type_id', '=', $existingActivity['activity_type_id'])
      ->addOrderBy('id', 'DESC')
      ->setLimit(1)
      ->execute()
      ->first();

      // If no intake numbers for activity type found, start at 001
      if(empty($mostRecent))
        $newIntake = '001';

      // Use regular expression to extract the year and the number at the end
      else if (preg_match('/^(\d{4})([AC])-?(\d+)$/', $mostRecent['CLIN_Adult_Intake_Activity_Data.Intake_Number'], $matches)) {
        $mostRecentYear = $matches[1];
        $lastIntakeNum = $matches[3];
        
        if($mostRecentYear === $year)
        {
          // Increment intake num by one
          $newIntake = str_pad((int)$lastIntakeNum + 1, 3, '0', STR_PAD_LEFT);
        }
        // If it is the start of a new year? Restart to 001
        else
          $newIntake = '001';
      }
    // Set intake number
    return $year . $prefix . '-' . $newIntake;
  }
}


