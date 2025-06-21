<?php
use CRM_SaceFeedbackForms_ExtensionUtil as E;

// group to hold auto-generated summary fields
return [
  [
    'name' => 'CustomGroup_Feedback_Summary',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Feedback_Summary',
        'title' => E::ts('Feedback Summary'),
        'extends' => 'Activity',
        // TODO: should only extend the summary activity type
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
];
