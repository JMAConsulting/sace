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
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mailchimpsync_civicrm_install() {
  _mailchimpsync_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mailchimpsync_civicrm_enable() {
  _mailchimpsync_civix_civicrm_enable();
}

// altered civix generated

/**
 * Define a permission administer_mailchimpsync
 *
 * Implements hook_civicrm_permission
 */
function mailchimpsync_civicrm_permission(&$permissions) {
  $permissions += [
    'administer_mailchimpsync' => [
      'label' => E::ts('Administer Mailchimpsync'),
      'description' => E::ts('Modify the settings of the mailchimpsync extension'),
    ],
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
  if ($objectName == 'contact') {
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
      'mailchimpsync__name_and_tags_sync_pre',
    ])
    ->addMethodCall('addListener', [
      'hook_mailchimpsync_data_updates_check',
      'mailchimpsync__name_and_tags_sync',
    ]);
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
function mailchimpsync__name_and_tags_sync_pre($event) {

  $ids = implode(',', $event->cache_entry_ids);

  // Get CiviCRM tags for these contacts as a string like <tagid>[,<tagid>]...
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

  // Load all contact names.
  $dao = CRM_Core_DAO::executeQuery(
    "SELECT civicrm_contact_id, first_name, last_name
    FROM civicrm_mailchimpsync_cache mcc
    INNER JOIN civicrm_contact ct
      ON mcc.civicrm_contact_id = ct.id
    WHERE mcc.id IN ($ids);");
  while ($dao->fetch()) {
    $event->pre_data['mcs_names'][$dao->civicrm_contact_id] = ['first_name' => $dao->first_name, 'last_name' => $dao->last_name];
  }
  $dao->free();

}

/**
 * Add names and tags to data updates.
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
function mailchimpsync__name_and_tags_sync($event) {
  /** @var CRM_Mailchimpsync_BAO_MailchimpsyncCache $cache_entry */
  $cache_entry = $event->cache_entry;
  $data = $cache_entry->getCiviCRMData();
  $mailchimp_data = $cache_entry->getMailchimpData();

  $changes = FALSE;

  // Load current names in CiviCRM.
  $names = $event->pre_data['mcs_names'][$cache_entry->civicrm_contact_id] ?? ['first_name' => '', 'last_name' => ''];

  if (FALSE) {
    // pretend MC has no names to force a push.
    // @todo expose this as an api param.
    unset($data['mcs_names']['previous']);
  }

  $namesInMailchimp = [
    'first_name' => $mailchimp_data['first_name'] ?? '',
    'last_name' => $mailchimp_data['last_name'] ?? '',
  ];
  if (($data['mcs_names']['previous'] ?? NULL) !== $names
    || $names !== $namesInMailchimp
  ) {
    // Names have changed in Civi since last push,
    // or names are different in Civi to Mailchimp.

    // store name for next time
    $data['mcs_names']['previous'] = $names;

    // Update FNAME, LNAME. Nb. `array_filter` will remove fields with empty() values.
    $data['mcs_names']['mailchimp_updates'] = [
      'merge_fields' => array_filter([
        'FNAME' => $names['first_name'],
        'LNAME' => $names['last_name'],
      ]),
    ];
    // Mark this entry as needing updates.
    $cache_entry->sync_status = 'todo';
    $changes = TRUE;
  }
  else {
    // Names have not changed. Clear out any updates from an earlier run.
    if (!empty($data['mcs_names']['mailchimp_updates'])) {
      $data['mcs_names']['mailchimp_updates'] = [];
      $changes = TRUE;
    }
  }

  // Tags:
  if (!empty($event->pre_data['mcs_tags']['all_tags'])) {

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
          'status' => (in_array($tag_id, $contacts_tags)) ? 'active' : 'inactive',
        ];
      }

      // Ensure this is going to be included in the sync.
      $cache_entry->sync_status = 'todo';
      $changes = TRUE;
    }
    else {
      // Tags unchanged, clear out updates from previous run
      if (!empty($data['mcs_tags']['tag_updates'])) {
        $data['mcs_tags']['tag_updates'] = [];
        $changes = TRUE;
      }
    }
  }

  if ($changes) {
    // Put the modified data back on the record.
    $event->cache_entry->civicrm_data = serialize($data);
    // Notify the calling code that this cache entry needs saving.
    // (we don't save it ourselves as other extns may also edit this).
    $event->needs_saving = TRUE;
  }
}

/**
 * Implements hook_civicrm_alterLogTables().
 *
 * Exclude tables from logging tables since they hold mostly temp data.
 */
function mailchimpsync_civicrm_alterLogTables(&$logTableSpec) {
  unset($logTableSpec['civicrm_mailchimpsync_batch']);
  unset($logTableSpec['civicrm_mailchimpsync_update']);
  unset($logTableSpec['civicrm_mailchimpsync_status']);
}
