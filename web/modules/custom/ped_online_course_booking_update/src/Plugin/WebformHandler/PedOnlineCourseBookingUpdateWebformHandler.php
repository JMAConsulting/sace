<?php

namespace Drupal\ped_online_course_booking_update\Plugin\WebformHandler;

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
 *   id = "ped_online_course_booking_update_handler",
 *   label = @Translation("PED Online Course Booking Update Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler for fixing Youth Online Course Additional Options"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class PedOnlineCourseBookingUpdateWebformHandler extends WebformHandlerBase {

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
    if ($webform_submission_data && $webform_submission_data['civicrm_1_activity_1_activity_activity_type_id'] == PED_ONLINE_COURSE_BOOKING_UPDATE_ADULT_ACTIVITY) {
      $webform_submission_data[PED_ONLINE_COURSE_BOOKING_UPDATE_OPTIONAL_ADDONS] = 'None';
      $webform_submission->setData($webform_submission_data);
    }
  }

}
