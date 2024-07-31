<?php

require_once 'locked_notes.civix.php';
// phpcs:disable
use CRM_LockedNotes_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function locked_notes_civicrm_config(&$config): void {
  _locked_notes_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function locked_notes_civicrm_install(): void {
  _locked_notes_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function locked_notes_civicrm_enable(): void {
  _locked_notes_civix_civicrm_enable();
}
