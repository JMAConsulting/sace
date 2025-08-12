<?php
use CRM_Activityassigneerole_ExtensionUtil as E;

return [
  'name' => 'ActivityRole',
  'table' => 'civicrm_activity_role',
  'class' => 'CRM_Activityassigneerole_DAO_ActivityRole',
  'getInfo' => fn() => [
    'title' => E::ts('Activity Role'),
    'title_plural' => E::ts('Activity Roles'),
    'description' => E::ts('FIXME'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique ActivityRole ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'assignee_contact_id' => [
      'title' => E::ts('Assignee Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to Contact'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'role_id' => [
      'title' => E::ts('Role ID'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Select',
      'required' => TRUE,
      'pseudoconstant' => [
        'option_group_name' => 'appointment_role',
      ],
    ],
    'activity_id' => [
      'title' => E::ts('Activity ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EnttiyRef',
      'required' => TRUE,
      'description' => E::ts('FK to Activity'),
      'entity_reference' => [
        'entity' => 'Activity',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
  ],
];
