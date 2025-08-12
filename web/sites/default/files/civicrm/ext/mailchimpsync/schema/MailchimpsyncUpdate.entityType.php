<?php
use CRM_Mailchimpsync_ExtensionUtil as E;
return [
  'name' => 'MailchimpsyncUpdate',
  'table' => 'civicrm_mailchimpsync_update',
  'class' => 'CRM_Mailchimpsync_DAO_MailchimpsyncUpdate',
  'getInfo' => fn() => [
    'title' => E::ts('Mailchimpsync Update'),
    'title_plural' => E::ts('Mailchimpsync Updates'),
    'description' => E::ts('This table keeps a copy of all updates sent or to be sent to Mailchimp.'),
    'log' => FALSE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique MailchimpsyncUpdate ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'mailchimpsync_cache_id' => [
      'title' => E::ts('Mailchimpsync Cache ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to MailchimpsyncCache ID'),
      'entity_reference' => [
        'entity' => 'MailchimpsyncCache',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'mailchimpsync_batch_id' => [
      'title' => E::ts('Mailchimpsync Batch ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Mailchimpsync Batch'),
      'entity_reference' => [
        'entity' => 'MailchimpsyncBatch',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'data' => [
      'title' => E::ts('Data'),
      'sql_type' => 'TEXT',
      'input_type' => NULL,
      'required' => TRUE,
    ],
    'completed' => [
      'title' => E::ts('Completed'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'default' => FALSE,
    ],
    'error_response' => [
      'title' => E::ts('Error Response'),
      'sql_type' => 'TEXT',
      'input_type' => NULL,
      'description' => E::ts('Set if the mailchimp update fails to whatever mailchimp returned.'),
    ],
    'created_date' => [
      'title' => E::ts('Created Date'),
      'sql_type' => 'timestamp',
      'input_type' => NULL,
      'description' => E::ts('When was the update created'),
      'default' => 'CURRENT_TIMESTAMP',
    ],
  ],
];
