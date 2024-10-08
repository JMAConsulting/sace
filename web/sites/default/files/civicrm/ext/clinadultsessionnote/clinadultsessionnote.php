<?php

require_once 'clinadultsessionnote.civix.php';

use CRM_Clinadultsessionnote_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function clinadultsessionnote_civicrm_config(&$config): void {
  _clinadultsessionnote_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function clinadultsessionnote_civicrm_install(): void {
  _clinadultsessionnote_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function clinadultsessionnote_civicrm_enable(): void {
  _clinadultsessionnote_civix_civicrm_enable();
}
