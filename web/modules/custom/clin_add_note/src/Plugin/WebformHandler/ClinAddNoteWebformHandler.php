<?php

namespace Drupal\clin_add_note\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\MimeType\MimeTypeGuesserInterface;

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

  protected $mimeTypeGuesser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->civicrm = $container->get('civicrm');
    $instance->database = \Drupal::database();
    $instance->mimeTypeGuesser = $container->get('file.mime_type.guesser');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $webform_submission_data = $webform_submission->getData();

    if ($webform_submission_data) {
      if($webform_submission_data['aid'] != '') {
        $note = \Civi\Api4\Note::create(FALSE)
          ->addValue('entity_table', 'civicrm_activity')
          ->addValue('contact_id', 'user_contact_id')
          ->addValue('note', $webform_submission_data['details'])
          ->addValue('entity_id', $webform_submission_data['aid'])
          ->addValue('subject', $webform_submission_data['subject'])
          ->execute()
          ->first();

        if(isset($webform_submission_data['is_locked']) && $webform_submission_data['is_locked']) {
          \Civi\Api4\LockedNote::create(FALSE)
            ->addValue('note_id', $note['id'])
            ->addValue('is_locked', TRUE)
            ->execute();
        }
        else {
          \Civi\Api4\LockedNote::create(FALSE)
            ->addValue('note_id', $note['id'])
            ->addValue('is_locked', FALSE)
            ->execute();
        }
      }
      elseif($webform_submission_data['nid'] != '') {
        $note = \Civi\Api4\Note::create(FALSE)
          ->addValue('entity_table', 'civicrm_note')
          ->addValue('contact_id', 'user_contact_id')
          ->addValue('note', $webform_submission_data['details'])
          ->addValue('entity_id', $webform_submission_data['nid'])
          ->addValue('subject', $webform_submission_data['subject'])
          ->execute()
          ->first();
      }

      elseif($webform_submission_data['cid'] != '') {
        $note = \Civi\Api4\Note::create(FALSE)
          ->addValue('entity_table', 'civicrm_contact')
          ->addValue('contact_id', 'user_contact_id')
          ->addValue('note', $webform_submission_data['details'])
          ->addValue('entity_id', $webform_submission_data['cid'])
          ->addValue('subject', $webform_submission_data['subject'])
          ->execute()
          ->first();
      }

      if(isset($webform_submission_data['upload_attachment'])) {
        foreach($webform_submission_data['upload_attachment'] as $attachment_id) {
          $file = \Drupal\file\Entity\File::load($attachment_id);
          if ($file) {
            $file_uri = $file->getFileUri();
            $mime_type = $this->mimeTypeGuesser->guessMimeType($file_uri);
            
            $attachment = \Civi\Api4\File::create(FALSE)
              ->addValue('file_type_id', 1)
              ->addValue('uri', $file_uri)
              ->addValue('mime_type', $mime_type)
              ->execute()
              ->first();

            $results = \Civi\Api4\EntityFile::create(FALSE)
              ->addValue('entity_table', 'civicrm_note')
              ->addValue('entity_id', $note['id'])
              ->addValue('file_id', $attachment['id'])
              ->execute();
          }
        }
      }
    }
  }
}
