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
  CRM_Core_DAO::executeQuery("TRUNCATE TABLE civicrm_prevnext_cache");
  // KG - can't do this CRM_Core_DAO::executeQuery("TRUNCATE TABLE civicrm_group_contact_cache");
  CRM_Core_DAO::executeQuery("TRUNCATE TABLE civicrm_acl_contact_cache");
  //  Not necessary if set the timeout to 0 on search prefs
//  CRM_Contact_BAO_GroupContactCache::deterministicCacheFlush();
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function noprevnext_civicrm_xmlMenu(&$files) {
  _noprevnext_civix_civicrm_xmlMenu($files);
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
  _noprevnext_civix_civicrm_postInstall();
  \Civi::settings()->set('smartGroupCacheTimeout', '0');
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function noprevnext_civicrm_uninstall() {
  _noprevnext_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function noprevnext_civicrm_enable() {
  _noprevnext_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function noprevnext_civicrm_disable() {
  _noprevnext_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function noprevnext_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _noprevnext_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function noprevnext_civicrm_managed(&$entities) {
  _noprevnext_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function noprevnext_civicrm_caseTypes(&$caseTypes) {
  _noprevnext_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function noprevnext_civicrm_angularModules(&$angularModules) {
  _noprevnext_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function noprevnext_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _noprevnext_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function noprevnext_civicrm_entityTypes(&$entityTypes) {
  _noprevnext_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_themes().
 */
function noprevnext_civicrm_themes(&$themes) {
  _noprevnext_civix_civicrm_themes($themes);
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
