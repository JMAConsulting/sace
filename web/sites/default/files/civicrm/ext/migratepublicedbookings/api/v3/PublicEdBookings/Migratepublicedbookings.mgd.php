<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'Cron:PublicEdBookings.Migratepublicedbookings',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Call PublicEdBookings.Migratepublicedbookings API',
      'description' => 'Call PublicEdBookings.Migratepublicedbookings API',
      'run_frequency' => 'Daily',
      'api_entity' => 'PublicEdBookings',
      'api_action' => 'Migratepublicedbookings',
      'parameters' => 'table_name=public_ed_bookings_2019',
    ],
  ],
];
