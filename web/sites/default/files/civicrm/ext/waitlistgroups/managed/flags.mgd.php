<?php
use CRM_Waitlistgroups_ExtensionUtil as E;

return [
  [
      'name' => 'cg_extend_objects:GroupContact',
      'entity' => 'OptionValue',
      'cleanup' => 'always',
      'update' => 'always',
      'params' => [
      'version' => 4,
      'values' => [
          'option_group_id.name' => 'cg_extend_objects',
          'label' => E::ts('Group Contacts'),
          'value' => 'GroupContact',
          'name' => 'civicrm_group_contact',
          'is_reserved' => TRUE,
          'is_active' => TRUE,
          'grouping' => 'group_id',
          ],
      ],
  ],
  [
    'name' => 'CustomGroup_Waitlist_Flags',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Waitlist_Flags',
        'title' => E::ts('Waitlist Flags'),
        'extends' => 'GroupContact',
        'style' => 'Inline',
        'help_pre' => '',
        'help_post' => '',
        'collapse_adv_display' => TRUE,
        'icon' => '',
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_Waitlist_Flags_Flags',
    'entity' => 'OptionGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Waitlist_Flags_Flags',
        'title' => E::ts('Waitlist Flags :: Flags'),
        'data_type' => 'String',
        'is_reserved' => FALSE,
        'option_value_fields' => [
          'name',
          'label',
          'description',
        ],
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_Waitlist_Flags_Flags_OptionValue_Crisis_Flag',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'Waitlist_Flags_Flags',
        'label' => E::ts('Crisis Flag'),
        'value' => '1',
        'name' => 'Crisis_Flag',
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_Waitlist_Flags_CustomField_Flags',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'Waitlist_Flags',
        'name' => 'Flags',
        'label' => E::ts('Flags'),
        'html_type' => 'CheckBox',
        'text_length' => 255,
        'note_columns' => 60,
        'note_rows' => 4,
        'option_group_id.name' => 'Waitlist_Flags_Flags',
        'serialize' => 1,
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
