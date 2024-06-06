<?php

namespace Drupal\clin_add_note\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace CiviCRM Activity Update Handler.
 *
 * @WebformHandler(
 *   id = "clin_add_note_handler",
 *   label = @Translation("CLIN Add Note Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler for populating notes on the Add Note to Appointment form"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class ClinAddNoteWebformHandler extends WebformHandlerBase {

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
      $note = \Civi\Api4\Note::create(FALSE)
        ->addValue('entity_table', 'civicrm_activity')
        ->addValue('contact_id', 'user_contact_id')
        ->addValue('note', $webform_submission_data['details'])
        ->addValue('entity_id', $webform_submission_data['aid'])
        ->addValue('subject', $webform_submission_data['subject'])
        ->execute();
    }
  }
}
