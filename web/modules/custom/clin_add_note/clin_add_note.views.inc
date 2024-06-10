
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

$data['civicrm_entity_file']['table']['base'] = array(
  'field' => 'id',
  'title' => t('CiviCRM Entity Files'),
  'help' => t("View displays CiviCRM Attachments create link to download"),
);
//Numeric File ID
$data['civicrm_entity_file']['id'] = array(
  'title' => t('Entity File ID'),
  'help' => t('The numeric ID entity file record of the Attachment'),
  'field' => array(
    'handler' => 'views_handler_field_numeric',
    'click sortable' => TRUE,
  ),
  'argument' => array(
    'handler' => 'views_handler_argument_numeric',
    'numeric' => TRUE,
  ),
  'filter' => array(
    'handler' => 'views_handler_filter_numeric',
  ),
  'sort' => array(
    'handler' => 'views_handler_sort',
  ),
);
  //EntityTable
$data['civicrm_entity_file']['entity_table'] = array(
  'title' => t('Entity Table'),
  'help' => t('The content type associated with the attachment'),
  'field' => array(
    'handler' => 'civicrm_handler_field',
    'click sortable' => TRUE,
  ),
);
//Entity ID
$data['civicrm_entity_file']['file_id'] = array(
  'title' => t('File ID'),
  'help' => t('FK to the ID of the file in civicrm_file table'),
  'field' => array(
    'handler' => 'views_handler_field_numeric',
    'click sortable' => TRUE,
  ),
);
//Entity ID
$data['civicrm_entity_file']['file_id'] = array(
  'title' => t('File ID'),
  'help' => t('FK to the ID of the file in civicrm_file table'),
  'field' => array(
    'handler' => 'views_handler_field_numeric',
    'click sortable' => TRUE,
  ),
);

  $data['civicrm_file']['table']['group'] = t('CiviCRM Files');

      $data['civicrm_file']['table']['base'] = array(
        // 
        'field' => 'id',
        'title' => t('CiviCRM Files'),
        'help' => t("View displays CiviCRM Attachments"),
      );
      //Numeric File ID
      $data['civicrm_file']['id'] = array(
        'title' => t('File ID'),
        'help' => t('The numeric ID of the Attachment'),
        'field' => array(
          'handler' => 'views_handler_field_numeric',
          'click sortable' => TRUE,
        ),
        'argument' => array(
          'handler' => 'views_handler_argument_numeric',
          'numeric' => TRUE,
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_numeric',
        ),
        'sort' => array(
          'handler' => 'views_handler_sort',
        ),
      );
        //URI
      $data['civicrm_file']['uri'] = array(
        'title' => t('URI'),
        'help' => t('The URI of the Attachment'),
        'field' => array(
          'handler' => 'views_handler_field_file',
          'click sortable' => TRUE,
        ),
      );
      //File Upload Date
      $data['civicrm_file']['upload_date'] = array(
        'title' => t('File Upload Date'),
        'help' => t('The date the Attachment was uploaded'),
        'field' => array(
          'handler' => 'civicrm_handler_field_datetime',
          'click sortable' => TRUE,
        ),
        'filter' => array(
          'handler' => 'civicrm_handler_filter_datetime',
        ),
        'sort' => array(
          'handler' => 'civicrm_handler_sort_date',
        ),
      );
      //File Description
      $data['civicrm_file']['description'] = array(
        'title' => t('Description'),
        'help' => t('The description of the Attachment'),
        'field' => array(
          'handler' => 'civicrm_handler_field',
          'click sortable' => TRUE,
        ),
        'argument' => array(
          'handler' => 'views_handler_argument_string',
          'numeric' => TRUE,
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_string',
        ),
        'sort' => array(
          'handler' => 'views_handler_sort',
        ),
      );
      //File Mime Type
      $data['civicrm_file']['mime_type'] = array(
        'title' => t('Mime Type'),
        'help' => t('The mime type of the Attachment'),
        'field' => array(
        'handler' => 'views_handler_field_file_filemime',
        'click sortable' => TRUE,
       ),
     );

     //check this join....

  $data['civicrm_file']['table']['join']['civicrm_note'] = array(
    'left_table' => 'civicrm_entity_file',
    'left_field' => 'file_id',
    'field' => 'id',
  );

  $data['civicrm_entity_file']['table']['join']['civicrm_note'] = array(
    // Directly links to file table
    'left_field' => 'id',
    'field' => 'entity_id',
    'extra' => array(
      array(
        'field' => 'entity_table',
        'value' => 'civicrm_note',
        'numeric' => FALSE,
      ),
    ),
  );
}
