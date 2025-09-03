<?php

namespace Drupal\ped_evaluation_summary\Plugin\WebformHandler;

use Civi\Api4\Activity;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace CiviCRM Evaluation Summary Request Handler.
 *
 * @WebformHandler(
 *   id = "evaluation_summary_handler",
 *   label = @Translation("Evaluation Summary Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler for adding customisation in all Presentation Booking request forms"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */

class EvaluationSummaryWebformHandler extends WebformHandlerBase {

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
        $data = unserialize($row['civicrm_data']);
        $activity = Activity::get(FALSE)
          ->addSelect('source_contact_id', 'target_contact_id')
          ->addWhere('id', '=', $data['activity'][1]['id'])
          ->execute()
          ->first();

        $id = Activity::create(FALSE)
          ->addValue('activity_type_id:name', 'PED - Presentation Evaluation Summary Score')
          ->addValue('PED_Booking_Reference.Booking_Reference_ID', $data['activity'][1]['id'])
          ->addValue('status_id:name', 'Pending')
          ->addValue('source_contact_id', $activity['source_contact_id'])
          ->addValue('target_contact_id', $activity['target_contact_id'])
          ->addValue('PED_Presentation_Evaluation_Summary_Score.Result_Verified_by', $activity['source_contact_id'])
          ->execute()
          ->first()['id'];

        Activity::update(FALSE)
          ->addWhere('id', '=', $activity['id'])
          ->addValue('Booking_Information.Presentation_Evaluation_Summary_Activity_ID', $id)
          ->execute();
      }
    }

  }

}
