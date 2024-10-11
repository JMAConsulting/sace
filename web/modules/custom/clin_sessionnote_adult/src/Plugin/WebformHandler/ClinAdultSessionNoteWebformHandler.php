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

    $therapy_mapping = [
      "Client-Centered" => 1,
      "Feminist Therapy" => 2,
      "ACT" => 3,
      "Attachment-Based" => 4,
      "CBT" => 5,
      "DBT" => 6,
      "EFT" => 7,
      "EMDR" => 8,
      "IFS/Part work" => 9,
      "Mindfulness" => 10,
      "Movement" => 18,
      "Narrative" => 11,
      "Play Therapy" => 12,
      "Psychodynamic" => 13,
      "Sandtray" => 14,
      "Solution Focused" => 15,
      "Somatic" => 16,
      "Other" => 17
    ];

    $modalities_array = $webform_submission_data['modalities_used_custom_drupal'];
    foreach ($modalities_array as $row) {
      // Get each value
      $modality = $row['modalities'];
      $intervention = $row['intervention'];
      $client_response = $row['client_response'];
      
      // POST request to civicrm Database
      $results = \Civi\Api4\Activity::create(FALSE)
      ->addValue('CLIN_Session_Note_Adult_Custom_Fields.Modalities_Used', [
        $modality,
      ])
      ->addValue('CLIN_Session_Note_Adult_Custom_Fields.Intervention', $intervention)
      ->addValue('CLIN_Session_Note_Adult_Custom_Fields.Client_Response_Modalities', $client_response)
      ->execute();
    }

  }
}