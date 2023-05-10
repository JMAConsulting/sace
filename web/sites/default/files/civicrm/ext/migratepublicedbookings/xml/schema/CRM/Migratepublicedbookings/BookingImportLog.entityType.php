<?php
// This file declares a new entity type. For more details, see "hook_civicrm_entityTypes" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
return [
  [
    'name' => 'BookingImportLog',
    'class' => 'CRM_Migratepublicedbookings_DAO_BookingImportLog',
    'table' => 'civicrm_booking_import_log',
  ],
];
