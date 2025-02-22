<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'CRM_SaceCivireports_Form_Report_PublicEdBookingsReport',
    'entity' => 'ReportTemplate',
    'params' => [
      'version' => 3,
      'label' => 'Public Education Presentations report',
      'description' => 'Public Education Presentations report template (publicedbookingsreport)',
      'class_name' => 'CRM_SaceCivireports_Form_Report_PublicEdBookingsReport',
      'report_url' => 'sace-civi-reports/publicedbookingsreport',
      'component' => '',
    ],
  ],
];
