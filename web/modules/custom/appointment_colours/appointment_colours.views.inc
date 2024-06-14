
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

  $data['civicrm_appointment_colours']['activity_type_id']['relationship'] = [
    'base' => 'civicrm_activity',
    'base_field' => 'activity_type_id',
    'field' => 'activity_type_id',
    'id' => 'standard',
    'label' => t('Activty Appointment Colours Join'),
  ];

  $data['civicrm_activity']['appointment_colours_activity_type_id'] = [
    'title' => t('Appointment Colours Activity Type ID'),
    'relationship' => [
      'base' => 'civicrm_appointment_colours',
      'base_field' => 'activity_type_id',
      'field' => 'activity_type_id',
      'id' => 'standard',
      'label' => t('Appointment Colours Join'),
    ],
  ];
  // Expose the colour_hex field
  $data['civicrm_activity']['colour_hex'] = [
    'title' => t('Colour Hex'),
    'help' => t('Hex code of the colour associated with the activity.'),
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
    'relationship' => [
      'title' => t('Activity Appointment Colours Join'),
      'label' => t('Appointment Colours Join'),
      'base' => 'civicrm_appointment_colours',
      'base_field' => 'activity_type_id',
      'field' => 'activity_type_id',
      'id' => 'standard',
    ],
  ];
}
