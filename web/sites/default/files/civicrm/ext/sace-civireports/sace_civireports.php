<?php

require_once 'sace_civireports.civix.php';
// phpcs:disable
use CRM_SaceCivireports_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function sace_civireports_civicrm_config(&$config) {
  _sace_civireports_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function sace_civireports_civicrm_xmlMenu(&$files) {
  _sace_civireports_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function sace_civireports_civicrm_install() {
  _sace_civireports_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function sace_civireports_civicrm_postInstall() {
  _sace_civireports_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function sace_civireports_civicrm_uninstall() {
  _sace_civireports_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function sace_civireports_civicrm_enable() {
  _sace_civireports_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function sace_civireports_civicrm_disable() {
  _sace_civireports_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function sace_civireports_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _sace_civireports_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function sace_civireports_civicrm_managed(&$entities) {
  _sace_civireports_civix_civicrm_managed($entities);
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
function sace_civireports_civicrm_caseTypes(&$caseTypes) {
  _sace_civireports_civix_civicrm_caseTypes($caseTypes);
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
function sace_civireports_civicrm_angularModules(&$angularModules) {
  _sace_civireports_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function sace_civireports_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _sace_civireports_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function sace_civireports_civicrm_entityTypes(&$entityTypes) {
  _sace_civireports_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_themes().
 */
function sace_civireports_civicrm_themes(&$themes) {
  _sace_civireports_civix_civicrm_themes($themes);
}

function sace_civireports_civicrm_alterReportVar($type, &$columns, &$form) {
  if (get_class($form) == 'CRM_Report_Form_Contribute_Detail' && $type == 'columns') {
     $columns['civicrm_contact']['fields']['group'] = [
       'title' => ts('Group'),
       'dbAlias' => '(SELECT GROUP_CONCAT(g.title) FROM civicrm_group g INNER JOIN civicrm_group_contact_cache gc ON gc.group_id = g.id WHERE gc.contact_id = contact_civireport.id GROUP BY gc.contact_id)',
     ];
  }
}
// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function sace_civireports_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function sace_civireports_civicrm_navigationMenu(&$menu) {
//  _sace_civireports_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _sace_civireports_civix_navigationMenu($menu);
//}
