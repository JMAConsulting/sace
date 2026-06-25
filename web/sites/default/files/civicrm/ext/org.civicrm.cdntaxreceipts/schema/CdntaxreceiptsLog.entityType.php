<?php
use CRM_Cdntaxreceipts_ExtensionUtil as E;

return [
  'name' => 'CdntaxreceiptsLog',
  'table' => 'cdntaxreceipts_log',
  'class' => 'CRM_Cdntaxreceipts_DAO_CdntaxreceiptsLog',
  'getInfo' => fn() => [
    'title' => E::ts('CDN Tax Receipts Log'),
    'title_plural' => E::ts('CDN Tax Receipts Logs'),
    'description' => E::ts('Log of tax receipts issued.'),
    'log' => FALSE,
  ],
  'getIndices' => fn() => [
    'receipt_no' => [
      'fields' => [
        'receipt_no' => TRUE,
      ],
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('The internal id of the issuance.'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'receipt_no' => [
      'title' => E::ts('Receipt Number'),
      'sql_type' => 'varchar(128)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Receipt Number.'),
      'input_attrs' => [
        'size' => '30',
      ],
    ],
    'issued_on' => [
      'title' => E::ts('Issued On'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'required' => TRUE,
      'description' => E::ts('Timestamp of when the receipt was issued, or re-issued.'),
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'contact_id' => [
      'title' => E::ts('Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('CiviCRM contact id to whom the receipt is issued.'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
      ],
    ],
    'receipt_amount' => [
      'title' => E::ts('Receipt Amount'),
      'sql_type' => 'decimal(20,2)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Receiptable amount, total minus non-receiptable portion.'),
      'input_attrs' => [
        'label' => E::ts('Receipt Amount'),
      ],
    ],
    'is_duplicate' => [
      'title' => E::ts('Is Duplicate'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => E::ts('Boolean indicating whether this is a re-issue.'),
    ],
    'uid' => [
      'title' => E::ts('Uid'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('CMS user id of the person issuing the receipt.'),
    ],
    'ip' => [
      'title' => E::ts('IP'),
      'sql_type' => 'varchar(128)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('IP of the user who issued the receipt.'),
      'input_attrs' => [
        'size' => '30',
      ],
    ],
    'issue_type' => [
      'title' => E::ts('Issue Type'),
      'sql_type' => 'varchar(16)',
      'input_type' => 'Checkbox',
      'required' => TRUE,
      'description' => E::ts('The type of receipt (single or annual).'),
    ],
    'issue_method' => [
      'title' => E::ts('Issue Method'),
      'sql_type' => 'varchar(16)',
      'input_type' => 'Checkbox',
      'description' => E::ts('The send method (email or print).'),
      'default' => NULL,
    ],
    'receipt_status' => [
      'title' => E::ts('Receipt Status'),
      'sql_type' => 'varchar(10)',
      'input_type' => 'Checkbox',
      'description' => E::ts('The status of the receipt (issued or cancelled)'),
      'default' => 'issued',
    ],
    'email_tracking_id' => [
      'title' => E::ts('Email Tracking ID'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Text',
      'description' => E::ts('A unique id to track email opens.'),
      'default' => NULL,
      'input_attrs' => [
        'size' => '64',
      ],
    ],
    'email_opened' => [
      'title' => E::ts('Email Opened Date'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'description' => E::ts('Timestamp an email open event was detected.'),
      'default' => NULL,
    ],
    'location_issued' => [
      'title' => E::ts('Location Issued'),
      'sql_type' => 'varchar(32)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('City where receipt was issued.'),
      'default' => '',
      'input_attrs' => [
        'size' => '64',
      ],
    ],
  ],
];
