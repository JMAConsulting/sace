<?php

require_once 'sace_feedback_forms.civix.php';

use CRM_SaceFeedbackForms_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function sace_feedback_forms_civicrm_config(&$config): void {
  _sace_feedback_forms_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function sace_feedback_forms_civicrm_install(): void {
  _sace_feedback_forms_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function sace_feedback_forms_civicrm_enable(): void {
  _sace_feedback_forms_civix_civicrm_enable();
}
