<?php
use CRM_Mailchimpsync_ExtensionUtil as E;
return [
  'name' => 'MailchimpsyncBatch',
  'table' => 'civicrm_mailchimpsync_batch',
  'class' => 'CRM_Mailchimpsync_DAO_MailchimpsyncBatch',
  'getInfo' => fn() => [
    'title' => E::ts('Mailchimpsync Batch'),
    'title_plural' => E::ts('Mailchimpsync Batches'),
    'description' => E::ts('Holds details about Mailchimp Batches - basically a cache'),
    'log' => FALSE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique MailchimpsyncBatch ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'mailchimp_list_id' => [
      'title' => E::ts('Mailchimp List ID'),
      'sql_type' => 'varchar(32)',
      'input_type' => 'Text',
      'description' => E::ts('We batch per list/audience'),
    ],
    'mailchimp_batch_id' => [
      'title' => E::ts('Mailchimp Batch ID'),
      'sql_type' => 'varchar(32)',
      'input_type' => 'Text',
      'description' => E::ts('Mailchimp-supplied Batch ID'),
    ],
    'status' => [
      'title' => E::ts('Status'),
      'sql_type' => 'varchar(32)',
      'input_type' => 'Text',
      'description' => E::ts('Mailchimp-supplied status'),
    ],
    'submitted_at' => [
      'title' => E::ts('Submitted At'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
    ],
    'completed_at' => [
      'title' => E::ts('Completed At'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'description' => E::ts('Mailchimp-supplied date of completion'),
    ],
    'finished_operations' => [
      'title' => E::ts('Finished Operations'),
      'sql_type' => 'int',
      'input_type' => 'Number',
      'default' => 0,
    ],
    'errored_operations' => [
      'title' => E::ts('Errored Operations'),
      'sql_type' => 'int',
      'input_type' => 'Number',
      'default' => 0,
    ],
    'total_operations' => [
      'title' => E::ts('Total Operations'),
      'sql_type' => 'int',
      'input_type' => 'Number',
      'default' => 0,
    ],
    'response_processed' => [
      'title' => E::ts('Response Processed'),
      'sql_type' => 'int',
      'input_type' => 'Number',
      'description' => E::ts('Set to 1 when processing, 2 when processed'),
      'default' => 0,
    ],
  ],
];
