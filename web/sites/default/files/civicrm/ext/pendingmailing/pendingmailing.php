<?php

require_once 'pendingmailing.civix.php';
use CRM_Pendingmailing_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function pendingmailing_civicrm_config(&$config) {
  _pendingmailing_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function pendingmailing_civicrm_install() {
  _pendingmailing_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function pendingmailing_civicrm_postInstall() {
  _pendingmailing_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function pendingmailing_civicrm_uninstall() {
  _pendingmailing_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function pendingmailing_civicrm_enable() {
  _pendingmailing_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function pendingmailing_civicrm_disable() {
  _pendingmailing_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function pendingmailing_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _pendingmailing_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function pendingmailing_civicrm_entityTypes(&$entityTypes) {
  _pendingmailing_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_buildForm().
 */
function pendingmailing_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Mailing_Form_Search') {
    $parent = $form->controller->getParent();
    $type = ($parent->_sms ? 'sms' : 'mailing');

    $job_id = CRM_Pendingmailing_BAO_Pendingmailing::getJobId($type);

    if (!$job_id) {
      Civi::log()->warning('pendingmailing: could not find the job_id for Job.proess_mailing');
      return;
    }

    $last_run = CRM_Pendingmailing_BAO_Pendingmailing::getLastRun($job_id);

    if ($pending = CRM_Pendingmailing_BAO_Pendingmailing::hasPendingMailing($type)) {
      $human = CRM_Pendingmailing_BAO_Pendingmailing::elapsedSecondsToHuman($pending);

      if (CRM_Core_Permission::check('administer CiviCRM')) {
        $message = E::ts('You have a mailing pending. The delivery will begin in the next 15 minutes. Last run time: %1. If it is urgent, you can <a %2>click here to start it now.</a> Keep the page open while it runs. Please only use this if it is urgent. The normal cron that runs every 15 minutes will be much faster for large mailings.', [
          1 => $last_run,
          2 => 'class="crm-popup" href="' . CRM_Utils_System::url('civicrm/admin/job', 'action=view&id=' . $job_id . '&reset=1') . '"',
        ]);

        CRM_Core_Session::setStatus($message, E::ts('Mailing pending (for %1)', [1 => $human]), 'alert', ['expires' => 0]);
      }
      else {
        $message = E::ts('You have a mailing pending. The delivery will begin in the next 15 minutes. Last run time: %1', [
          1 => $last_run,
        ]);
        CRM_Core_Session::setStatus($message, E::ts('Mailing pending (for %1)', [1 => $human]), 'alert', ['expires' => 0]);
      }
    }
  }
}
