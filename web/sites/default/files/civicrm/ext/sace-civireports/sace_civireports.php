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
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function sace_civireports_civicrm_install() {
  _sace_civireports_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function sace_civireports_civicrm_enable() {
  _sace_civireports_civix_civicrm_enable();
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
