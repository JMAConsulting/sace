<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return [
  0 => 
  [
    'name' => 'CRM_Cdntaxreceipts_Form_Report_ReceiptsIssued',
    'entity' => 'ReportTemplate',
    'params' => 
    [
      'version' => 3,
      'label' => 'Tax Receipts - Receipts Issued',
      'description' => 'Tax Receipts - ReceiptsIssued (org.civicrm.cdntaxreceipts)',
      'class_name' => 'CRM_Cdntaxreceipts_Form_Report_ReceiptsIssued',
      'report_url' => 'cdntaxreceipts/receiptsissued',
      'component' => 'CiviContribute',
    ],
  ],
];
