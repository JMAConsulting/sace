<?php

require_once 'selectivelogging.civix.php';

use CRM_Selectivelogging_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function selectivelogging_civicrm_config(&$config): void {
  _selectivelogging_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function selectivelogging_civicrm_install(): void {
  _selectivelogging_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function selectivelogging_civicrm_enable(): void {
  _selectivelogging_civix_civicrm_enable();
}
