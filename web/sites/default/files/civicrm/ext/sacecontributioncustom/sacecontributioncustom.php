<?php

require_once 'sacecontributioncustom.civix.php';
// phpcs:disable
use CRM_Sacecontributioncustom_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function sacecontributioncustom_civicrm_config(&$config) {
  _sacecontributioncustom_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function sacecontributioncustom_civicrm_install() {
  _sacecontributioncustom_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function sacecontributioncustom_civicrm_postInstall() {
  _sacecontributioncustom_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function sacecontributioncustom_civicrm_uninstall() {
  _sacecontributioncustom_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function sacecontributioncustom_civicrm_enable() {
  _sacecontributioncustom_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function sacecontributioncustom_civicrm_disable() {
  _sacecontributioncustom_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function sacecontributioncustom_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _sacecontributioncustom_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function sacecontributioncustom_civicrm_entityTypes(&$entityTypes) {
  _sacecontributioncustom_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function sacecontributioncustom_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function sacecontributioncustom_civicrm_navigationMenu(&$menu) {
//  _sacecontributioncustom_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _sacecontributioncustom_civix_navigationMenu($menu);
//}


function sacecontributioncustom_civicrm_buildForm($formName, &$form) {
 
  if($formName == 'CRM_Contribute_Form_Contribution_Main' && ($form->getVar('_id') == 7 || $form->getVar('_id') == 8 || $form->getVar('_id') == 9)) {
    Civi::resources()->addScriptFile('sacecontributioncustom', 'js/community.js');
    Civi::resources()->addStyleFile('sacecontributioncustom', 'css/forms.css');
  }

  if($formName == 'CRM_Contribute_Form_Contribution_Confirm' && ($form->getVar('_id') == 7 || $form->getVar('_id') == 8 || $form->getVar('_id') == 9)) {
    Civi::resources()->addStyleFile('sacecontributioncustom', 'css/confirms.css');
    Civi::resources()->addScriptFile('sacecontributioncustom', 'js/confirms.js');
  }
}
