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
function appointment_colours_views_data_alter(&$data) {
  $data['civicrm_appointment_colours']['table']['group'] = t('CiviCRM Appointment Colours');

  $data['civicrm_appointment_colours']['table']['base'] = [
    'field' => 'id',
    'title' => t('CiviCRM Entity Files'),
    'help' => t('View displays CiviCRM Attachments create link to download'),
  ];

  $data['civicrm_appointment_colours']['actvity_type_id'] = [
    'title' => t('Activity Type ID'),
    'help' => t('The Activity Type ID associated with the colour'),
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
  $data['civicrm_appointment_colours']['colour_hex'] = [
    'title' => t('Colour Hex'),
    'help' => t('Hex code of the colour associated with the activity.'),
    'field' => [
      'id' => 'standard',
      'click sortable' => TRUE,
    ],
  ];


  $data['civicrm_appointment_colours']['table']['join']['civicrm_activity'] = [
    'left_field' => 'activity_type_id',
    'field' => 'activity_type_id',
  ];
}
