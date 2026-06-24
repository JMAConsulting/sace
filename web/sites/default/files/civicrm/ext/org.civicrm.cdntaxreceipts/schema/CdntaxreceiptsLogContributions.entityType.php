<?php
use CRM_Cdntaxreceipts_ExtensionUtil as E;

return [
  'name' => 'CdntaxreceiptsLogContributions',
  'table' => 'cdntaxreceipts_log_contributions',
  'class' => 'CRM_Cdntaxreceipts_DAO_CdntaxreceiptsLogContributions',
  'getInfo' => fn() => [
    'title' => E::ts('CDN Tax Receipts Log Contribution'),
    'title_plural' => E::ts('CDN Tax Receipts Log Contributions'),
    'description' => E::ts('Contribution(s) for each tax receipt issued.'),
    'log' => FALSE,
  ],
  'getIndices' => fn() => [
    'contribution_id' => [
      'fields' => [
        'contribution_id' => TRUE,
      ],
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('The internal id of this line.'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'receipt_id' => [
      'title' => E::ts('Receipt ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('The internal receipt ID this line belongs to.'),
      'entity_reference' => [
        'entity' => 'CdntaxreceiptsLog',
        'key' => 'id',
      ],
    ],
    'contribution_id' => [
      'title' => E::ts('Contribution ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('CiviCRM contribution id for which the receipt is issued.'),
      'input_attrs' => [
        'label' => E::ts('Contribution'),
      ],
    ],
    'contribution_amount' => [
      'title' => E::ts('Contribution Amount'),
      'sql_type' => 'decimal(20,2)',
      'input_type' => 'Text',
      'description' => E::ts('Total contribution amount.'),
      'default' => '0.0',
    ],
    'receipt_amount' => [
      'title' => E::ts('Receipt Amount'),
      'sql_type' => 'decimal(20,2)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Receiptable amount, total minus non-receiptable portion.'),
    ],
    'receive_date' => [
      'title' => E::ts('Contribution Date'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'required' => TRUE,
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
  ],
];
