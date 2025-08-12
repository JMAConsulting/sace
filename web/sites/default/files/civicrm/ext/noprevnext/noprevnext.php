<?php

require_once 'noprevnext.civix.php';
use CRM_Noprevnext_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function noprevnext_civicrm_config(&$config) {
  _noprevnext_civix_civicrm_config($config);

  // This is maybe too broad - it clears everyone's cache.
  //CRM_Core_DAO::executeQuery("TRUNCATE TABLE civicrm_prevnext_cache");
  // KG - can't do this CRM_Core_DAO::executeQuery("TRUNCATE TABLE civicrm_group_contact_cache");
  //CRM_Core_DAO::executeQuery("TRUNCATE TABLE civicrm_acl_contact_cache");
  //  Not necessary if set the timeout to 0 on search prefs
//  CRM_Contact_BAO_GroupContactCache::deterministicCacheFlush();
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function noprevnext_civicrm_install() {
  _noprevnext_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function noprevnext_civicrm_postInstall() {
  \Civi::settings()->set('smartGroupCacheTimeout', '0');
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function noprevnext_civicrm_enable() {
  _noprevnext_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function noprevnext_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function noprevnext_civicrm_navigationMenu(&$menu) {
//  _noprevnext_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _noprevnext_civix_navigationMenu($menu);
//}
