<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'CRM_SaceCivireports_Form_Report_ExtendingContributionDetail',
    'entity' => 'ReportTemplate',
    'params' => [
      'version' => 3,
      'label' => 'ExtendingContributionDetail',
      'description' => 'ExtendingContributionDetail (sace-civireports)',
      'class_name' => 'CRM_SaceCivireports_Form_Report_ExtendingContributionDetail',
      'report_url' => 'sace-civireports/extendingcontributiondetail',
      'component' => 'CiviContribute',
    ],
  ],
];
