<?php

require_once 'waitlistgroups.civix.php';

use CRM_Waitlistgroups_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function waitlistgroups_civicrm_config(&$config): void {
  _waitlistgroups_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function waitlistgroups_civicrm_install(): void {
  _waitlistgroups_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function waitlistgroups_civicrm_enable(): void {
  _waitlistgroups_civix_civicrm_enable();
}
