<?php

require_once 'sace.civix.php';

use CRM_Sace_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function sace_civicrm_config(&$config): void {
  _sace_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function sace_civicrm_install(): void {
  _sace_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function sace_civicrm_enable(): void {
  _sace_civix_civicrm_enable();
}

function sace_civicrm_copy($objectName, &$object, $original_id = NULL): void {
  if ($objectName == 'Activity' && !empty($object->duration) && !empty($object->activity_date_time) && !empty($object->id)) {
    $endDate = strtotime($object->activity_date_time . ' + ' . $object->duration . ' minute');
    \Civi\Api4\Activity::update(FALSE)
     ->addWhere('id', '=', $object->id)
     ->addValue('Booking_Information.End_Date', date('YmdHis', $endDate))
     ->execute();
  }
}
