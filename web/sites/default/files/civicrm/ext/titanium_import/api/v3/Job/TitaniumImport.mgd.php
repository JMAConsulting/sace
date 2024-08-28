<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'Cron:Job.ClientImport',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Call Job.ClientImport API',
      'description' => 'Imports Titanium client data into CiviCRM.',
      'run_frequency' => 'Daily',
      'api_entity' => 'Job',
      'api_action' => 'client_import',
      'parameters' => '',
    ],
  ],
  [
    'name' => 'Cron:Job.AppointmentImport',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Call Job.AppointmentImport API',
      'description' => 'Imports Titanium appointment data into CiviCRM.',
      'run_frequency' => 'Daily',
      'api_entity' => 'Job',
      'api_action' => 'appointment_import',
      'parameters' => '',
    ],
  ],
  [
    'name' => 'Cron:Job.FlagImport',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Call Job.FlagImport API',
      'description' => 'Imports Titanium flag data into CiviCRM.',
      'run_frequency' => 'Daily',
      'api_entity' => 'Job',
      'api_action' => 'flag_import',
      'parameters' => '',
    ],
  ],
];
