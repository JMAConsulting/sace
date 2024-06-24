<?php

namespace Drupal\clin_flags\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace CiviCRM Activity Update Handler.
 *
 * @WebformHandler(
 *   id = "clin_add_flags_handler",
 *   label = @Translation("CLIN AddFlags Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler for fixing the CLIN AddFlags Form"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class ClinAddFlagsWebformHandler extends WebformHandlerBase {

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
      $optionValue = \Civi\Api4\OptionValue::get(TRUE)
      ->addWhere('option_group_id', '=', 2)
      ->addWhere('id', '=',  $webform_submission_data['type_of_flag'])
      ->execute()
      ->first();

      if(isset($webform_submission_data['existing_flag_id']) && is_numeric($webform_submission_data['existing_flag_id'])) {
        $results = \Civi\Api4\Activity::update(TRUE)
          ->addWhere('id', '=', $webform_submission_data['existing_flag_id'])
          ->addValue('activity_type_id', $optionValue['value'])
          ->addValue('subject', $webform_submission_data['flag_name'])
          ->addValue('details', $webform_submission_data['description'])
          ->execute();
      }

      else {
        $results = \Civi\Api4\Activity::create(TRUE)
          ->addValue('activity_type_id', $optionValue['value'])
          ->addValue('source_contact_id', $webform_submission_data['civicrm_1_contact_1_contact_existing'])
          ->addValue('target_contact_id', $webform_submission_data['civicrm_2_contact_1_contact_existing'])
          ->addValue('subject', $webform_submission_data['flag_name'])
          ->addValue('details', $webform_submission_data['description'])
          ->execute();
      }

    }
  }
}
