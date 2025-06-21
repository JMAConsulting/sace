<?php
use CRM_SaceFeedbackForms_ExtensionUtil as E;

return [
// NOTE: this group is not managed yet
//  [
//    'name' => 'CustomGroup_Booking_Information',
//    'entity' => 'CustomGroup',
//    'cleanup' => 'unused',
//    'update' => 'unmodified',
//    'params' => [
//      'version' => 4,
//      'values' => [
//        'name' => 'Booking_Information',
//        'title' => E::ts('CE - Booking Information'),
//        'extends' => 'Activity',
//        'extends_entity_column_value' => [],
//        'style' => 'Inline',
//        'help_pre' => E::ts(''),
//        'help_post' => E::ts(''),
//        'weight' => 6,
//        'created_date' => '2020-04-26 15:28:13',
//        'icon' => '',
//      ],
//      'match' => ['name'],
//    ],
//  ],
  [
    'name' => 'CustomGroup_Booking_Information_CustomField_Feedback_Webform',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'Booking_Information',
        'name' => 'Feedback_Webform',
        'label' => E::ts('Feedback Webform'),
        'html_type' => 'Text',
        'text_length' => 255,
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
