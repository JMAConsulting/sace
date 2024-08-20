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
      $appointment = \Civi\Api4\Activity::get(FALSE)
        ->addSelect('id')
        ->addWhere('source_contact_id', '=', $webform_submission_data['civicrm_1_contact_1_contact_existing'])
        ->addWhere('activity_type_id', '=', $webform_submission_data['civicrm_1_activity_1_activity_activity_type_id'])
        ->addOrderBy('id', 'DESC')
        ->execute()
        ->first();
        
      // Update appointment with any counsellors assigned
      $results = \Civi\Api4\Activity::update(FALSE)
        ->addValue('assignee_contact_id', $webform_submission_data['select_counsellor'])
        ->addWhere('id', '=', $appointment['id'])
        ->execute();
    }
  }
}
