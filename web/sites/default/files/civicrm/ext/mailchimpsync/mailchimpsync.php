<?php

require_once 'mailchimpsync.civix.php';
use CRM_Mailchimpsync_ExtensionUtil as E;

// untouched civix generated:
/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mailchimpsync_civicrm_config(&$config) {
  _mailchimpsync_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function mailchimpsync_civicrm_xmlMenu(&$files) {
  _mailchimpsync_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mailchimpsync_civicrm_install() {
  _mailchimpsync_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function mailchimpsync_civicrm_postInstall() {
  _mailchimpsync_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function mailchimpsync_civicrm_uninstall() {
  _mailchimpsync_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mailchimpsync_civicrm_enable() {
  _mailchimpsync_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function mailchimpsync_civicrm_disable() {
  _mailchimpsync_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function mailchimpsync_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mailchimpsync_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function mailchimpsync_civicrm_managed(&$entities) {
  _mailchimpsync_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function mailchimpsync_civicrm_caseTypes(&$caseTypes) {
  _mailchimpsync_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function mailchimpsync_civicrm_angularModules(&$angularModules) {
  _mailchimpsync_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function mailchimpsync_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mailchimpsync_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function mailchimpsync_civicrm_entityTypes(&$entityTypes) {
  _mailchimpsync_civix_civicrm_entityTypes($entityTypes);
}

// altered civix generated
/**
 * Define a permission administer_mailchimpsync
 *
 * Implements hook_civicrm_permission
 */
function mailchimpsync_civicrm_permission(&$permissions) {
  $permissions += [
    'administer_mailchimpsync' => E::ts('Administer Mailchimpsync'),
  ];
}
/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function mailchimpsync_civicrm_navigationMenu(&$menu) {
  _mailchimpsync_civix_insert_navigation_menu($menu, 'Administer/System Settings', [
    'label'      => E::ts('Configure Mailchimp Sync'),
    'name'       => 'mailchimpsync_config',
    'url'        => 'civicrm/a/#/mailchimpsync/config',
    'permission' => 'administer_mailchimpsync',
    'operator'   => 'OR',
    'separator'  => 0,
  ]);
  _mailchimpsync_civix_insert_navigation_menu($menu, 'Mailings', [
    'label'      => E::ts('Mailchimp Sync Status'),
    'name'       => 'mailchimpsync_config',
    'url'        => 'civicrm/a/#/mailchimpsync',
    'permission' => 'access CiviMail',
    'operator'   => 'OR',
    'separator'  => 0,
  ]);
  _mailchimpsync_civix_navigationMenu($menu);
}

// Other CiviCRM hooks
/**
 * Add 'Include in next Mailchimp Sync' to the search tasks for contacts.
 *
 * Implements hook_civicrm_searchTasks.
 *
 * @param string $objectName
 * @param array &$tasks
 */
function mailchimpsync_civicrm_searchTasks($objectName, &$tasks) {
  if($objectName == 'contact'){
    $tasks[] = [
      'title' => 'Include in next Mailchimp Sync',
      'class' => 'CRM_Mailchimpsync_Form_Task_ScheduleResync',
    ];
  }
}

/**
 * Implements hook_civicrm_container
 *
 * We register 2 hooks to copy CiviCRM tags to Mailchimp.
 *
 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
 */
function mailchimpsync_civicrm_container($container) {
  $container->findDefinition('dispatcher')
            ->addMethodCall('addListener', [
              'hook_mailchimpsync_data_updates_check_pre',
              'mailchimpsync__tags_sync_pre'])
            ->addMethodCall('addListener', [
              'hook_mailchimpsync_data_updates_check',
              'mailchimpsync__tags_sync']);
}
// Custom hooks
/**
 * Get cache of tags in RAM
 *
 * Implements hook_mailchimpsync_data_updates_check_pre
 *
 * @param Civi\Core\Event\GenericHookEvent
 *    This object has the following keys:
 *    - 'audience'        CRM_Mailchimpsync_Audience
 *    - 'cache_entry_ids' Array of integer mailchimpsync_cache_id values
 *                        We can trust that these are integers, for SQL safety purposes
 *    - 'pre_data'        array - we store our results in here, under the 'mcs_tags' key.
 */
function mailchimpsync__tags_sync_pre($event) {

  $ids = implode(',', $event->cache_entry_ids);

  // Get CiviCRM tags for these cotacts as a string like <tagid>[,<tagid>]...
  $event->pre_data['mcs_tags']['contact_tags'] = CRM_Core_DAO::executeQuery(
    "SELECT civicrm_contact_id, GROUP_CONCAT(tag_id ORDER BY tag_id SEPARATOR ',') tags
    FROM civicrm_mailchimpsync_cache mcc
    INNER JOIN civicrm_entity_tag et
      ON mcc.civicrm_contact_id = et.entity_id
         AND et.entity_table='civicrm_contact'
    WHERE mcc.id IN ($ids)
    GROUP BY civicrm_contact_id;"
  )->fetchMap('civicrm_contact_id', 'tags');

  // Get list of all CiviCRM tags for contacts.
  $event->pre_data['mcs_tags']['all_tags'] = CRM_Core_DAO::executeQuery(
    "SELECT id, name FROM civicrm_tag
    WHERE used_for='civicrm_contact'")
  ->fetchMap('id', 'name');

}
/**
 * Add tags to data updates.
 *
 * Implements hook_mailchimpsync_data_updates_check
 *
 * Uses the 'mcs_tags' key to store its metadata which is under a key called
 * 'previous' and is the ordered concatenation of a contact's tag IDs.
 *
 * This enables us to compare the current tags with the previous, saving
 * a pointless tag_updates entry if it's unchanged.
 *
 * The event object has the following keys:
 * - 'audience',
 * - 'cache_entry',
 * - 'pre_data',
 * - 'needs_saving'
 *
 * @param Civi\Core\Event\GenericHookEvent
 */
function mailchimpsync__tags_sync($event) {
  $data = unserialize($event->cache_entry->civicrm_data);
  $cache_entry = $event->cache_entry;


  if (empty($event->pre_data['mcs_tags']['all_tags'])) {
    // No tags registered in CiviCRM!
    return;
  }

  $contacts_tags = $event->pre_data['mcs_tags']['contact_tags'][$cache_entry->civicrm_contact_id] ?? '';
  if (!isset($data['mcs_tags']['previous']) || $data['mcs_tags']['previous'] !== $contacts_tags) {
    // Tags have changed.

    // Store our raw tags list for next time.
    $data['mcs_tags']['previous'] = $contacts_tags;

    // We'll need to provide a full set of updates to all
    // CiviCRM tags.
    $data['mcs_tags']['tag_updates'] = [];

    // Convert to array.
    $contacts_tags = $contacts_tags ? explode(',', $contacts_tags) : [];

    // Store the updates in tag_updates in the mailchimp API expected format.
    foreach ($event->pre_data['mcs_tags']['all_tags'] as $tag_id => $tag_name) {
      $data['mcs_tags']['tag_updates'][] = [
        'name'   => 'CiviCRM: ' . $tag_name,
        'status' => (in_array($tag_id, $contacts_tags)) ? 'active' : 'inactive'
      ];
    }

    // Ensure this is going to be included in the sync.
    if ($cache_entry->sync_status !== 'todo') {
      $cache_entry->sync_status = 'todo';
    }

    // Notify the calling code that this cache entry needs saving.
    // (we don't save it ourselves as other extns may also edit this).
    $event->needs_saving = TRUE;
  }
  // else: Tags have not changed. So get rid of any updates left from last time

  if ($event->needs_saving) {
    // Put the modified data back on the record.
    $event->cache_entry->civicrm_data = serialize($data);
  }
}
