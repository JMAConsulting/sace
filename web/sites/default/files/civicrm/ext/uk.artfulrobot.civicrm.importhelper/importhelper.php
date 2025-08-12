<?php

require_once 'importhelper.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function importhelper_civicrm_config(&$config) {
  _importhelper_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function importhelper_civicrm_install() {
  $dao = CRM_Core_DAO::executeQuery(
    "CREATE TABLE IF NOT EXISTS `civicrm_csv_match_cache` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `contact_id` int(10) unsigned NOT NULL DEFAULT '0',
      `fname` varchar(50) NOT NULL DEFAULT '',
      `lname` varchar(100) NOT NULL DEFAULT '',
      `email` varchar(255) NOT NULL DEFAULT '',
      `title` varchar(20) NOT NULL DEFAULT '',
      `state` varchar(12) NOT NULL DEFAULT '',
      `resolution` varchar(4096) NOT NULL DEFAULT '',
      `data` varchar(4096) NOT NULL DEFAULT '',
      PRIMARY KEY (`id`),
      KEY `foo` (`state`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cache of uploaded data file'
  ");

  _importhelper_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function importhelper_civicrm_uninstall() {
  // Remove our table.
  $dao = CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS `civicrm_csv_match_cache`;");
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function importhelper_civicrm_enable() {
  _importhelper_civix_civicrm_enable();
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *

 // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function importhelper_civicrm_navigationMenu(&$menu) {
  //$parentID =  CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');

  _importhelper_civix_insert_navigation_menu($menu, 'Contacts', array(
    'label' => ts('CSV Import Helper', array('domain' => 'uk.artfulrobot.civicrm.importhelper')),
    'name' => 'csvimporthelper',
    'url' => 'civicrm/a/#csv-import-helper',
    'permission' => 'import contacts',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _importhelper_civix_navigationMenu($menu);
}
/**
 * Implements HOOK_civicrm_alterAPIPermissions.
 *
 * https://wiki.civicrm.org/confluence/display/CRMDOC/API+Security
 * https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_alterAPIPermissions/
 *
 */
function importhelper_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $permissions['csv_helper']['default'] = ['access CiviCRM', 'view all contacts'];
}
