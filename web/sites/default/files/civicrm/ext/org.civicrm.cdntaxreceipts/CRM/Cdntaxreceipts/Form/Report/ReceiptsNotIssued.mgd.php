<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return [
  0 => 
  [
    'name' => 'CRM_Cdntaxreceipts_Form_Report_ReceiptsNotIssued',
    'entity' => 'ReportTemplate',
    'params' => 
    [
      'version' => 3,
      'label' => 'Tax Receipts - Receipts Not Issued',
      'description' => 'Tax Receipts - Receipts Not Issued (org.civicrm.cdntaxreceipts)',
      'class_name' => 'CRM_Cdntaxreceipts_Form_Report_ReceiptsNotIssued',
      'report_url' => 'cdntaxreceipts/receiptsnotissued',
      'component' => 'CiviContribute',
    ],
  ],
];
