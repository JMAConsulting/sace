<?php

namespace Drupal\ped_online_course_booking_request\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Civi\Api4\EntityTag;

/**
 * Sace CiviCRM Activity Online Course Booking Request Handler.
 *
 * @WebformHandler(
 *   id = "ped_online_course_booking_reqest_handler",
 *   label = @Translation("PED Online Course Booking Reqest Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler for fixing Youth Online Course Additional Options"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class PedOnlineCourseBookingRequestWebformHandler extends WebformHandlerBase {

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
  public function preSave(WebformSubmissionInterface $webform_submission) {
    $webform_submission_data = $webform_submission->getData();
    // If we are dealing with an Adult Online Course then set the field to be None
    if ($webform_submission_data && $webform_submission_data['civicrm_1_activity_1_activity_activity_type_id'] == PED_ONLINE_COURSE_BOOKING_ADULT_ACTIVITY) {
      $webform_submission_data[PED_ONLINE_COURSE_BOOKING_OPTIONAL_ADDONS] = ['None'];
      $webform_submission->setData($webform_submission_data);
    }
    elseif ($webform_submission_data[PED_ONLINE_COURSE_BOOKING_OPTIONAL_ADDONS] === '' || $webform_submission_data[PED_ONLINE_COURSE_BOOKING_OPTIONAL_ADDONS] === '0' || $webform_submission_data[PED_ONLINE_COURSE_BOOKING_OPTIONAL_ADDONS] == []) {
      // if We have not filled out the Optional Addons section and its a Youth Booking set it to be none also
      $webform_submission_data[PED_ONLINE_COURSE_BOOKING_OPTIONAL_ADDONS] = ['None'];
      $webform_submission->setData($webform_submission_data);
    }
  }

}
