<?php

namespace Drupal\clin_sessionnote_adult\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Civi\Api4\EntityTag;

/**
 * Sace CiviCRM Clin Adult Session Note Handler.
 *
 * @WebformHandler(
 *   id = "clin_sessionnote_adult_handler",
 *   label = @Translation("CLIN Session Note Adult Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler for submitting CLIN adult session notes"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class ClinAdultSessionNoteWebformHandler extends WebformHandlerBase {
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
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $webform_submission_data = $webform_submission->getData();
    \CRM_Core_Error::debug_var('Civicrm Data', $this->civicrm);
    \CRM_Core_Error::debug_var('Webform Submission', $webform_submission_data);

    $modalities_array = $webform_submission_data['modalities_used_custom_drupal'];
    
    $modalities_used_checkbox = [];
    $results = \Civi\Api4\Activity::create(TRUE)
    ->addValue('activity_type_id', 194)
    ->addValue('source_contact_id', 'user_contact_id')
    ->execute();
    foreach ($modalities_array as $row) {
      // Get each value
      $modality_suffix = $row['modalities'];
      $intervention_data = $row['intervention'];
      $client_response_data = $row['client_response'];
      
      // Append to checkbox
      $modalities_used_checkbox[] = $modality_suffix;

      // POST text fields to civicrm database
      $results = \Civi\Api4\Activity::update(FALSE)
      ->addValue('CLIN_Session_Note_Adult_Custom_Fields.intervention' . $modality_suffix, $intervention_data)
      ->addValue('CLIN_Session_Note_Adult_Custom_Fields.client_response' . $modality_suffix, $client_response_data)
      ->addWhere('activity_type_id', '=', 194)
      ->addWhere('source_contact_id', '=', 'user_contact_id')
      ->execute();
    }
    $results = \Civi\Api4\Activity::update(FALSE)
    ->addValue('CLIN_Session_Note_Adult_Custom_Fields.Modalities_Used', $modalities_used_checkbox)
    ->addWhere('activity_type_id', '=', 194)
    ->addWhere('activity_type_id', '=', 'user_contact_id')
    ->execute();
  }
}