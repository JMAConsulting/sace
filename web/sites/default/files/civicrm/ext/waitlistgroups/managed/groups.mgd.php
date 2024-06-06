<?php
use CRM_Waitlistgroups_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_Waitlist_Group',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Waitlist_Group',
        'title' => E::ts('Waitlist Group'),
        'extends' => 'Group',
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
    'name' => 'CustomGroup_Waitlist_Group_CustomField_Waitlist_Group_',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'Waitlist_Group',
        'name' => 'Waitlist_Group_',
        'label' => E::ts('Waitlist Group?'),
        'data_type' => 'Boolean',
        'html_type' => 'Radio',
        'text_length' => 255,
        'note_columns' => 60,
        'note_rows' => 4,
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
