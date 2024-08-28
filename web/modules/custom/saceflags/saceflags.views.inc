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
function saceflags_views_data_alter(&$data) {

  $data['civicrm_saceflags']['activity_type_id']['relationship'] = [
    'base' => 'civicrm_activity',
    'base_field' => 'activity_type_id',
    'base field' => 'activity_type_id',
    'field' => 'activity_type_id',
    'id' => 'standard',
    'label' => t('Activty Saceflags Join'),
  ];

  $data['civicrm_activity']['saceflags_activity_type_id'] = [
    'title' => t('Saceflags Activity Type ID'),
    'relationship' => [
      'base' => 'civicrm_saceflags',
      'base_field' => 'activity_type_id',
      'base field' => 'activity_type_id',
      'field' => 'activity_type_id',
      'id' => 'standard',
      'label' => t('Saceflags Join'),
    ],
  ];

  $data['civicrm_option_value']['saceflagsvalue'] = [
    'title' => t('Saceflags Activity Type ID'),
    'relationship' => [
      'base' => 'civicrm_saceflags',
      'base_field' => 'activity_type_id',
      'base field' => 'activity_type_id',
      'field' => 'value',
      'id' => 'standard',
      'label' => t('Saceflags Join Option Value'),
    ],
  ];
}
