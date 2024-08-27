<?php

require_once 'titanium_import.civix.php';
// phpcs:disable
use CRM_TitaniumImport_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function titanium_import_civicrm_config(&$config): void {
  _titanium_import_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function titanium_import_civicrm_install(): void {
  _titanium_import_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function titanium_import_civicrm_enable(): void {
  _titanium_import_civix_civicrm_enable();
}
