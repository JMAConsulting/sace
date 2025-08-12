<?php
use CRM_Mailchimpsync_ExtensionUtil as E;
return [
  'name' => 'MailchimpsyncCache',
  'table' => 'civicrm_mailchimpsync_cache',
  'class' => 'CRM_Mailchimpsync_DAO_MailchimpsyncCache',
  'getInfo' => fn() => [
    'title' => E::ts('Mailchimpsync Cache'),
    'title_plural' => E::ts('Mailchimpsync Caches'),
    'description' => E::ts('Holds copies of data from Mailchimp and CiviCRM that assist with keeping both in sync.'),
    'log' => FALSE,
  ],
  'getIndices' => fn() => [
    'index_list_id_sync_status' => [
      'fields' => [
        'mailchimp_list_id' => TRUE,
        'sync_status' => TRUE,
      ],
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique MailchimpsyncCache ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'mailchimp_list_id' => [
      'title' => E::ts('Mailchimp List ID'),
      'sql_type' => 'varchar(32)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'mailchimp_member_id' => [
      'title' => E::ts('Mailchimp Member ID'),
      'sql_type' => 'char(32)',
      'input_type' => 'Text',
      'description' => E::ts('Theoretically redundant md5 of lower case email but Mailchimp has bugs'),
    ],
    'mailchimp_email' => [
      'title' => E::ts('Mailchimp Email'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'mailchimp_status' => [
      'title' => E::ts('Mailchimp Status'),
      'sql_type' => 'varchar(20)',
      'input_type' => 'Text',
      'description' => E::ts('subscribed|unsubscribed|cleaned|pending|transactional|archived'),
    ],
    'mailchimp_updated' => [
      'title' => E::ts('Mailchimp Updated'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'description' => E::ts('From API\'s last_changed field'),
    ],
    'mailchimp_data' => [
      'title' => E::ts('Mailchimp Data'),
      'sql_type' => 'blob',
      'input_type' => NULL,
      'description' => E::ts('PHP serialized data'),
    ],
    'civicrm_data' => [
      'title' => E::ts('Civicrm Data'),
      'sql_type' => 'blob',
      'input_type' => NULL,
      'description' => E::ts('PHP serialized data'),
    ],
    'civicrm_groups' => [
      'title' => E::ts('Civicrm Groups'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'description' => E::ts('Snapshot info about groups the contact has been added/removed since certain date, used by sync'),
    ],
    'civicrm_contact_id' => [
      'title' => E::ts('Civicrm Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Contact'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'sync_status' => [
      'title' => E::ts('Sync Status'),
      'sql_type' => 'varchar(10)',
      'input_type' => 'Text',
      'description' => E::ts('ok|todo|redo|fail'),
      'default' => 'todo',
    ],
  ],
];
