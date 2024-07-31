<?php

namespace Drupal\clin_add_note\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\MimeType\MimeTypeGuesserInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\Core\File\FileSystemInterface;

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
      // Adding note directly to appointment
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
      // Appending or editing a note
      elseif($webform_submission_data['nid'] != '') {
        if($webform_submission_data['action'] == 'edit')
        {
          $note = \Civi\Api4\Note::update(TRUE)
            ->addValue('note', $webform_submission_data['details'])
            ->addValue('subject', $webform_submission_data['subject'])
            ->addWhere('id', '=', $webform_submission_data['nid'])
            ->execute()
            ->first();
        }
        else {
          $note = \Civi\Api4\Note::create(FALSE)
            ->addValue('entity_table', 'civicrm_note')
            ->addValue('contact_id', 'user_contact_id')
            ->addValue('note', $webform_submission_data['details'])
            ->addValue('entity_id', $webform_submission_data['nid'])
            ->addValue('subject', $webform_submission_data['subject'])
            ->execute()
            ->first();
        }
      }
      // Adding a client note
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
        if($webform_submission_data['action'] == 'edit') {
          $attachments = \Civi\Api4\EntityFile::get(FALSE)
            ->addSelect('file.*')
            ->addJoin('File AS file', 'LEFT', ['file_id', '=', 'file.id'])
            ->addWhere('entity_table', '=', 'civicrm_note')
            ->addWhere('entity_id', '=', $note['id'])
            ->execute();

          $existingFiles = [];

          foreach($attachments as $attachment) {
            $existingFiles[] = get_file_id_from_uri($attachment['file.uri']);
          }

          $submittedFiles = $webform_submission_data['upload_attachment'];

          $addedFiles = array_diff($submittedFiles, $existingFiles);
          $deletedFiles = array_diff($existingFiles, $submittedFiles);

          // Process deleted files
          foreach ($deletedFiles as $file_id) {
            $file = File::load($file_id);
            if ($file) {
              $file_uri = $file->getFileUri();

              // Remove the file association from CiviCRM
              \Civi\Api4\EntityFile::delete(FALSE)
                ->addWhere('entity_table', '=', 'civicrm_note')
                ->addWhere('entity_id', '=', $note['id'])
                ->addWhere('file_id.uri', '=', $file_uri)
                ->execute();

              \Civi\Api4\File::delete(FALSE)
                ->addWhere('uri', '=', $file_uri)
                ->execute();
            }
          }
        } else {
          $addedFiles = $webform_submission_data['upload_attachment'];
        }

        // Handle newly added files
        foreach($addedFiles as $attachment_id) {
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

  private function get_file_id_from_uri($uri) {
    $file = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $uri]);
    return $file ? reset($file)->id() : null;
  }
}
