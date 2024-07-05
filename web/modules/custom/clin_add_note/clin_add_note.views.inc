<?php

use Drupal\civicrm_entity\CiviEntityStorage;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Render\Markup;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\FieldStorageConfigInterface;

/**
 * Implements hook_views_data_alter().
 *
 * @note
 * @note Copy and paste of views_views_data_alter to support our storage check.
 *
 * @see views_views_data()
 * @see views_views_data_alter()
 */
function clin_add_note_views_data_alter(&$data) {
  $data['civicrm_entity_file']['table']['group'] = t('CiviCRM Entity Files');

  $data['civicrm_entity_file']['table']['base'] = [
    'field' => 'id',
    'title' => t('CiviCRM Entity Files'),
    'help' => t('View displays CiviCRM Attachments create link to download'),
  ];

  // Numeric File ID
  $data['civicrm_entity_file']['id'] = [
    'title' => t('Entity File ID'),
    'help' => t('The numeric ID entity file record of the Attachment'),
    'field' => [
      'id' => 'standard',
      'click sortable' => TRUE,
    ],
    'argument' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'sort',
    ],
  ];

  // Entity Table
  $data['civicrm_entity_file']['entity_table'] = [
    'title' => t('Entity Table'),
    'help' => t('The content type associated with the attachment'),
    'field' => [
      'id' => 'standard',
      'click sortable' => TRUE,
    ],
  ];

  // Entity ID
  $data['civicrm_entity_file']['file_id'] = [
    'title' => t('File ID'),
    'help' => t('FK to the ID of the file in civicrm_file table'),
    'field' => [
      'id' => 'standard',
      'click sortable' => TRUE,
    ],
  ];

  $data['civicrm_file']['table']['group'] = t('CiviCRM Files');

  $data['civicrm_file']['table']['base'] = [
    'field' => 'id',
    'title' => t('CiviCRM Files'),
    'help' => t('View displays CiviCRM Attachments'),
  ];

  // Numeric File ID
  $data['civicrm_file']['id'] = [
    'title' => t('File ID'),
    'help' => t('The ID of the Attachment'),
    'field' => [
      'id' => 'standard',
      'click sortable' => TRUE,
    ],
    'argument' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'sort',
    ],
  ];

  // URI
  $data['civicrm_file']['uri'] = [
    'title' => t('URI'),
    'help' => t('The URI of the Attachment'),
    'field' => [
      'id' => 'standard',
      'click sortable' => TRUE,
    ],
  ];

  // File Upload Date
  $data['civicrm_file']['upload_date'] = [
    'title' => t('File Upload Date'),
    'help' => t('The date the Attachment was uploaded'),
    'field' => [
      'id' => 'date',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'id' => 'date',
    ],
    'sort' => [
      'id' => 'date',
    ],
  ];

  // File Description
  $data['civicrm_file']['description'] = [
    'title' => t('Description'),
    'help' => t('The description of the Attachment'),
    'field' => [
      'id' => 'standard',
      'click sortable' => TRUE,
    ],
    'argument' => [
      'id' => 'string_argument',
    ],
    'filter' => [
      'id' => 'string_filter',
    ],
    'sort' => [
      'id' => 'sort',
    ],
  ];

  // File Mime Type
  $data['civicrm_file']['mime_type'] = [
    'title' => t('Mime Type'),
    'help' => t('The mime type of the Attachment'),
    'field' => [
      'id' => 'standard',
      'click sortable' => TRUE,
    ],
  ];

  // Joins
  $data['civicrm_file']['table']['join']['civicrm_note'] = [
    'left_table' => 'civicrm_entity_file',
    'left_field' => 'file_id',
    'field' => 'id',
  ];

  $data['civicrm_entity_file']['table']['join']['civicrm_note'] = [
    'left_field' => 'id',
    'field' => 'entity_id',
    'extra' => [
      ['field' => 'entity_table', 'value' => 'civicrm_note', 'numeric' => FALSE],
    ],
  ];
}
