<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'CRM_SaceCivireports_Form_Report_ActivitySystem',
    'entity' => 'ReportTemplate',
    'params' => [
      'version' => 3,
      'label' => 'Activity System Report Template',
      'description' => 'ActivitySystem (sace-civireports)',
      'class_name' => 'CRM_SaceCivireports_Form_Report_ActivitySystem',
      'report_url' => 'sace-civireports/activity-system',
      'component' => NULL,
    ],
  ],
];
