<?php

use CRM_Cdntaxreceipts_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_Tax_Receipts_Receipts_Issued',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Tax_Receipts_Receipts_Issued',
        'label' => E::ts('Tax Receipts - Receipts Issued'),
        'form_values' => NULL,
        'mapping_id' => NULL,
        'search_custom_id' => NULL,
        'api_entity' => 'CdntaxreceiptsLog',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'contact_id.display_name',
            'issued_on',
            'receipt_amount',
            'receipt_no',
            'issue_type',
            'issue_method',
            'uid',
            'receipt_status',
            'email_opened',
            'GROUP_CONCAT(CdntaxreceiptsLog_CdntaxreceiptsLogContributions_receipt_id_01.contribution_id) AS GROUP_CONCAT_CdntaxreceiptsLog_CdntaxreceiptsLogContributions_receipt_id_01_contribution_id',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [
            'id',
          ],
          'join' => [
            [
              'CdntaxreceiptsLogContributions AS CdntaxreceiptsLog_CdntaxreceiptsLogContributions_receipt_id_01',
              'LEFT',
              [
                'id',
                '=',
                'CdntaxreceiptsLog_CdntaxreceiptsLogContributions_receipt_id_01.receipt_id',
              ],
            ],
          ],
          'having' => [],
        ],
        'expires_date' => NULL,
        'description' => NULL,
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_Tax_Receipts_Receipts_Issued_SearchDisplay_Tax_Receipts_Receipts_Issued_Table_1',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Tax_Receipts_Receipts_Issued_Table_1',
        'label' => E::ts('Tax Receipts - Receipts Issued'),
        'saved_search_id.name' => 'Tax_Receipts_Receipts_Issued',
        'type' => 'table',
        'settings' => [
          'description' => NULL,
          'sort' => [],
          'limit' => 50,
          'pager' => [],
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'contact_id.display_name',
              'dataType' => 'String',
              'label' => E::ts('Contact Name'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'issued_on',
              'dataType' => 'Timestamp',
              'label' => E::ts('Issued On'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
              // The time isn't too relevant, and suffers DST issues, so
              // use date-only.
              'format' => 'dateformatshortdate',
            ],
            [
              'type' => 'field',
              'key' => 'receipt_amount',
              'dataType' => 'Money',
              'label' => E::ts('Receipt Amount'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'receipt_no',
              'dataType' => 'String',
              'label' => E::ts('Receipt Number'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'issue_type',
              'dataType' => 'String',
              'label' => E::ts('Issue Type'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'issue_method',
              'dataType' => 'String',
              'label' => E::ts('Issue Method'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'uid',
              'dataType' => 'Integer',
              'label' => E::ts('Issued By (UID)'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => '',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'receipt_status',
              'dataType' => 'String',
              'label' => E::ts('Receipt Status'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'email_opened',
              'dataType' => 'Timestamp',
              'label' => E::ts('Email Opened Date'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'GROUP_CONCAT_CdntaxreceiptsLog_CdntaxreceiptsLogContributions_receipt_id_01_contribution_id',
              'dataType' => 'Integer',
              'label' => E::ts('Contribution ID'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'COUNT',
              ],
            ],
          ],
          'actions' => TRUE,
          'classes' => [
            'table',
            'table-striped',
          ],
          'headerCount' => TRUE,
          'tally' => [
            'label' => E::ts('Total'),
          ],
        ],
        'acl_bypass' => FALSE,
      ],
      'match' => [
        'name',
        'saved_search_id',
      ],
    ],
  ],
];
