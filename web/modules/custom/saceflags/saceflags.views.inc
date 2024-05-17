
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
  // Define the civicrm_saceflags table structure.
  $data['civicrm_saceflags'] = [
    'table' => [
      'group' => t('Saceflags'),
      'provider' => 'civicrm',
      'base' => [
        'field' => 'id',
        'title' => t('Saceflags'),
        'help' => t('Saceflags custom table containing flagging information.'),
      ],
    ],
    'fields' => [
      'id' => [
        'title' => t('Saceflags ID'),
        'help' => t('Unique Saceflags ID.'),
      ],
      'activity_type_id' => [
        'title' => t('Activity Type ID'),
        'help' => t('Activity Type ID from the activity table.'),
      ],
      'used_for_flagging_contacts' => [
        'title' => t('Used for Flagging Contacts'),
        'help' => t('Indicates whether this flag is used for flagging contacts.'),
      ],
    ],
  ];

  // Define relationship from civicrm_saceflags to civicrm_activity.
  $data['civicrm_saceflags']['activity_type_id']['relationship'] = [
    'base' => 'civicrm_activity',
    'base_field' => 'activity_type_id',
    'field' => 'activity_type_id',
    'id' => 'standard',
    'label' => t('Activity Saceflags Join'),
  ];

  // Define relationship from civicrm_activity to civicrm_saceflags.
  $data['civicrm_activity']['saceflags_activity_type_id'] = [
    'title' => t('Saceflags Activity Type ID'),
    'relationship' => [
      'base' => 'civicrm_saceflags',
      'base_field' => 'activity_type_id',
      'field' => 'activity_type_id',
      'id' => 'standard',
      'label' => t('Saceflags Join'),
    ],
  ];

  // Define relationship from civicrm_saceflags to civicrm_option_value.
  $data['civicrm_saceflags']['activity_type_option'] = [
    'title' => t('Activity Type Option Value'),
    'relationship' => [
      'base' => 'civicrm_option_value',
      'base_field' => 'value',
      'field' => 'activity_type_id',
      'id' => 'standard',
      'label' => t('Activity Type Option Value Join'),
      'extra' => [
        [
          'field' => 'option_group_id',
          'value' => '(SELECT id FROM {civicrm_option_group} WHERE name = "activity_type")',
        ],
      ],
    ],
  ];

  // Define relationship from civicrm_option_value to civicrm_saceflags.
  $data['civicrm_option_value']['saceflags_activity_type'] = [
    'title' => t('Saceflags Activity Type ID'),
    'relationship' => [
      'base' => 'civicrm_saceflags',
      'base_field' => 'activity_type_id',
      'field' => 'value',
      'id' => 'standard',
      'label' => t('Saceflags Join Option Value'),
      'extra' => [
        [
          'field' => 'option_group_id',
          'value' => '(SELECT id FROM {civicrm_option_group} WHERE name = "activity_type")',
        ],
      ],
    ],
  ];
}
