<?php
use CRM_SaceFeedbackForms_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_Feedback_Form',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Feedback_Form',
        'title' => E::ts('Feedback Form'),
        'extends' => 'Activity',
        //'extends_entity_column_value' => ['197'],
        'style' => 'Inline',
        'help_pre' => E::ts(''),
        'help_post' => E::ts(''),
        'weight' => 64,
        'collapse_adv_display' => TRUE,
        'created_date' => '2025-06-19 05:36:42',
        'icon' => '',
      ],
      'match' => ['name'],
    ],
  ],
  [
    'name' => 'CustomGroup_Feedback_Form_CustomField_Booking',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'Feedback_Form',
        'name' => 'Booking',
        'label' => E::ts('Booking'),
        'data_type' => 'EntityReference',
        'html_type' => 'Autocomplete-Select',
        'text_length' => 255,
        'note_columns' => 60,
        'note_rows' => 4,
        'column_name' => 'booking_1442',
        'fk_entity' => 'Activity',
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
