<?php
/**
 * Class to represent a Mailchimp Audience(list) that is synced with CiviCRM.
 *
 * This handles the config for this audience, too.
 *
 */
class CRM_Mailchimpsync_Audience
{
  /** @var string */
  protected $mailchimp_list_id;

  /** @var array */
  protected $config;

  /** Cached mailchimpsync_audience_status_* value */
  protected $status_cache;

  /**
   * Create an Audience object.
   *
   * Nb. you can call this with a list that is not yet configured. To know
   * you're fetching a configured audience, use newFromListId()
   *
   * @param string $list_id
   */
  protected function __construct(string $list_id) {
    if (!preg_match('/^[0-9a-zA-Z_]+$/', $list_id)) {
      throw new \InvalidArgumentException("Invalid Mailchimp list id: '$list_id'");
    }
    $config = CRM_Mailchimpsync::getConfig();
    $this->mailchimp_list_id = $list_id;
    $this->config = $config['lists'][$list_id]
      ?? [
        'subscriptionGroup' => 0,
        'api_key' => NULL,
      ];
  }

  /**
   * Constructor wrapper that checks we have the list ID.
   */
  public static function newFromListId($list_id) {
    $config = CRM_Mailchimpsync::getConfig();

    if (!isset($config['lists'][$list_id])) {
      throw new \InvalidArgumentException("List '$list_id' is not configured.");
    }

    $obj = new static($list_id);
    return $obj;
  }
  /**
   * Instantiate object given CiviCRM group ID.
   *
   * @throws InvalidArgumentException if group ID not found in config.
   *
   * @return CRM_Mailchimpsync_Audience
   */
  public static function newFromGroupId($group_id) {
    $config = CRM_Mailchimpsync::getConfig();
    foreach ($config['lists'] ?? [] as $list_id => $list_config) {
      if ($group_id == $list_config['subscriptionGroup']) {
        return static::newFromListId($list_id);
      }
    }
    throw new \InvalidArgumentException("Unknown group ID `$group_id`");
  }
  /**
   * Setter for List ID.
   *
   * @param string $list_id
   *
   * @return CRM_Mailchimpsync_Audience
   */
  public function setListId($list_id) {
    $this->mailchimp_list_id = $list_id;
    return $this;
  }
  /**
   * Setter for CiviCRM Group.
   *
   * @param int $group_id
   *
   * @return CRM_Mailchimpsync_Audience
   */
  public function setSubscriptionGroup($group_id) {
    $this->config['subscriptionGroup'] = $group_id;
    return $this;
  }
  /**
   * Getter for List ID.
   *
   * @return string
   */
  public function getListId() {
    return $this->mailchimp_list_id;
  }
  /**
   * Getter for CiviCRM Group.
   *
   * @return int
   */
  public function getSubscriptionGroup() {
    if (empty($this->config['subscriptionGroup'])) {
      throw new \Exception("No subscription group configured for list $this->mailchimp_list_id");
    }
    return (int) $this->config['subscriptionGroup'];
  }
  /**
   * Return the config for this list from mailchimpsync_config setting.
   * @return array
   */
  public function getConfig() {
    return $this->config;
  }
  /**
   * Update/set the config for this list from mailchimpsync_config setting.
   * @return CRM_Mailchimpsync_Audience $this
   */
  public function setConfig($local_config) {

    // Ensure we have defaults.
    $local_config += [
        'subscriptionGroup' => 0,
        'api_key' => NULL,
        'interests' => [],
    ];
    $global_config = CRM_Mailchimpsync::getConfig();
    $global_config['lists'][$this->mailchimp_list_id] = $local_config;
    CRM_Mailchimpsync::setConfig($global_config);

    // Update our cache of the config.
    $this->config = $local_config;

    return $this;
  }
  /**
   * This function deals with the fetch and reconciliation phases of the sync.
   *
   * Things have to be completed in order, but we have to break it up into jobs
   * that can run within a reasonable time limit. If called by a CLI script
   * it will just all run through one after another, but otherwise it can be re-run
   * and pick up where it left off.
   *
   * @param array $params with optional keys:
   * - time_limit:  stop after this number of seconds. Default is 1 week (assumed to basically mean no time limit).
   * - since:       a fixed since date that strtotime understands. Or pass an empty string meaning no limit, fetch all.
   *                if not present in parame, defaults to date last sync date.
   * - stop_on:     name the ready state you want to stop processing on, e.g. readyToSubmitUpdates
   * - with_data:   If set, do a data sync.
   */
  public function fetchAndReconcile($params) {

    $params += ['time_limit' => 604800];
    $stop_time = time() + $params['time_limit'];
    $with_data = (!empty($params['with_data']));

    // Calculate 'since' option.
    $relevant_since = $this->getRelevantSinceDate($params);

    $remaining_time = $stop_time - time();

    while ($remaining_time > 0) {
      $status = $this->getStatus();
      $current_state = $status['locks']['fetchAndReconcile'] ?? NULL;

      if (($params['stop_on'] ?? 'no stopping') === $current_state) {
        $this->log("Stopped as requested at step $params[stop_on]");
        return; // exit the loop completely.
      }

      switch ($current_state) {

      // Busy states - this is to prevent a 2nd cron job firing over the top of this one.
      case 'busy':
        $this->log("Called but locks say process already busy. Will not do anything.");
        return; // exit.

      case NULL:
      case 'readyToFetch':
        $merge_mailchimp_params = ['max_time' => $remaining_time];
        if ($relevant_since !== FALSE) {
          $merge_mailchimp_params['since'] = $relevant_since;
        }
        $this->mergeMailchimpData($merge_mailchimp_params);
        break;

      case 'readyToFixContactIds':
        // Assume these two are quick enough to run together as one; they're all SQL driven.
        $this->updateLock([
          'for'    => 'fetchAndReconcile',
          'to'     => 'busy',
          'andLog' => 'Beginning to remove invalid contact IDs and populate missing ones from email matches.'
        ]);
        $this->removeInvalidContactIds();
        $this->log("removeInvalidContactIds: beginning populateMissingContactIds");
        $result = $this->populateMissingContactIds();
        $this->log("removeInvalidContactIds: completed populateMissingContactIds: "
          . json_encode($result, JSON_PRETTY_PRINT));
        $this->updateLock([
          'for'    => 'fetchAndReconcile',
          'to'     => 'readyToCreateNewContactsFromMailchimp',
        ]);
        break;

      case 'readyToCreateNewContactsFromMailchimp':
        $total = $this->createNewContactsFromMailchimp($remaining_time);
        break;

      case 'readyToCleanUpDuplicates':
        $total = $this->cleanUpDuplicatesInCache();
        break;

      case 'readyToAddCiviOnly':
        $total = $this->addCiviOnly();
        break;

      case 'readyToCheckForGroupChanges':
        // Insert data step after this.
        $next = $with_data ? 'readyToCheckForDataUpdates' : 'readyToReconcileQueue';
        $this->identifyGroupContactChanges($relevant_since, $next);
        break;

      case 'readyToCheckForDataUpdates':
        $this->checkForDataUpdates($remaining_time, $relevant_since);
        break;

      case 'readyToReconcileQueue':
        $stats = $this->reconcileQueueProcess($remaining_time, $relevant_since, $with_data);
        break;

      case 'readyToSubmitUpdates':
        $stats = $this->submitUpdatesBatches($remaining_time);
        if ($stats['is_complete']) {
          $this->log('fetchAndReconcile process is complete.');
        }
        return;
        // break 2;

      default:
        throw new Exception("Invalid lock: {$status['locks']['fetchAndReconcile']}");
      }

      // Update remaining time.
      $remaining_time = $stop_time - time();
    }

    if ($remaining_time <= 0) {
      $this->log('Stopped processing as out of time.');
    }
  }
  /**
   * Abort a sync.
   */
  public function abortSync() {
    // First, find any batches to do with this sync and try to cancel them at Mailchimp.
    $batches = CRM_Core_DAO::executeQuery(
      'SELECT mailchimp_batch_id b
       FROM civicrm_mailchimpsync_batch
       WHERE mailchimp_list_id = %1 AND status != "finished"',
      [1 => [$this->mailchimp_list_id, 'String']])->fetchMap('b', 'b');
    $api = $this->getMailchimpApi();
    foreach ($batches as $batch_id) {
      $api->delete("batches/$batch_id");
    }

    // Any live sync updates: set the cache status to fail and the update to completed with error.
    CRM_Core_DAO::executeQuery(
      'UPDATE civicrm_mailchimpsync_cache c
      INNER JOIN civicrm_mailchimpsync_update u ON c.id = u.mailchimpsync_cache_id
      SET sync_status = "fail", error_response = "Sync was aborted", completed = 1
      WHERE u.completed = 0 AND c.mailchimp_list_id = %1',
      [1 => [$this->mailchimp_list_id, 'String']]
    );

    // Update our batch record(s) to 'aborted'
    CRM_Core_DAO::executeQuery(
      'UPDATE civicrm_mailchimpsync_batch
      SET status = "aborted"
       WHERE mailchimp_list_id = %1 AND status != "finished"',
      [1 => [$this->mailchimp_list_id, 'String']]);

    // Finally, release any locks.
    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => 'readyToFetch',
      'andLog' => 'Abort!',
      'andAlso' => function(&$s) {
        $s['fetch']['offset'] = 0;
      }
    ]);
  }
  // The following are mostly internal.
  // The following methods deal with the 'fetch' phase
  /**
   * Merge subscriber data form Mailchimp into our table.
   *
   * @param array $params with keys:
   * - since    Only load things changed since this date (optional)
   *            Nb. this is only used when doing the first chunk of the fetch
   *            We then store it in our status and subsequent calls use that.
   *
   * - max_time Don't continue to fetch another batch if it has already taken
   *            longer than this number of seconds.
   *
   */
  public function mergeMailchimpData(array $params=[]) {

    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => 'busy',
      'andLog' => 'mergeMailchimpData: beginning to fetch data from mailchimp.'
    ]);

    try {
      $status = $this->getStatus();

      // 1 week max excution time, expected to be equivalent to no max time.
      $params += ['max_time' => 60*60*24*7];
      $stop_time = time() + $params['max_time'];

      $api = $this->getMailchimpApi();

      $query = [
        'count'  => $api->max_members_to_fetch,
        'offset' => ($status['fetch']['offset'] ?? 0),
      ];

      if ($query['offset'] === 0) {
        // This is the start of a new sync process.
        // - Update the latest sync time.
        // - empty the previous log.
        // - store a 'since' date that will persist through multiple calls
        //   of fetchAndReconcile
        if (!empty($params['since'])) {
          $query['since_last_changed'] = date(DATE_ISO8601, $params['since']);
        }

        $this->updateAudienceStatusSetting(function(&$c) use ($query) {
          $c['lastSyncTime'] = date('Y-m-d H:i:s');
          $c['log'] = [];
          $c['fetch']['since'] = $query['since_last_changed'] ?? '';
        });
      }
      else {
        // This is a subsequent call. Load initial 'since' config.
        if ($status['fetch']['since'] ?? '') {
          $query['since_last_changed'] = $status['fetch']['since'];
        }
      }

      do {
        $this->log("fetchMergeMailchimpData: fetching up to $api->max_members_to_fetch records from offset $query[offset] "
          . (($query['since_last_changed'] ?? '') ? ' since ' . $query['since_last_changed'] : ''));
        $response = $api->get("lists/$this->mailchimp_list_id/members", $query);

        // Insert it into our cache table.
        foreach ($response['members'] ?? [] as $member) {
          $this->mergeMailchimpMember($member);
        }

        // Prepare to load next page.
        $query['offset'] += $api->max_members_to_fetch;

        $more_to_fetch = $response['total_items'] - $query['offset'];

        if ($more_to_fetch > 0) {
          $this->updateAudienceStatusSetting(function(&$c) use ($query) {
            // Store current offset, total to do.
            $c['fetch']['offset'] = $query['offset'];
          });
        }

      } while ($more_to_fetch>0 && time() < $stop_time);
    }
    catch (Exception $e) {
      // Release lock (to allow fetch to try again) before rethrowing.
      $this->log('fetchMergeMailchimpData: ERROR: ' . $e->getMessage());
      $this->updateLock(['for' => 'fetchAndReconcile', 'to' => 'readyToFetch']);
      throw $e;
    }

    // Successful finish, we're now ready to reconcile.
    if ($more_to_fetch>0) {
      $this->updateLock([
        'for'    => 'fetchAndReconcile',
        'to'     => 'readyToFetch',
        'andLog' => "fetchMergeMailchimpData: stopping but $more_to_fetch more to fetch" ]);
    }
    else {
      // We've fetched everything.
      $this->updateLock([
        'for'     => 'fetchAndReconcile',
        'to'      => 'readyToFixContactIds',
        'andLog'  => "fetchMergeMailchimpData: completed ($response[total_items] fetched).",
        // Reset the offset for next time.
        'andAlso' => function(&$c) { $c['fetch']['offset'] = 0; }
      ]);
    }
  }
  /**
   * Copy data from mailchimp into our table.
   *
   * @param object $member
   *
   * @return CRM_Mailchimpsync_BAO_MailchimpsyncCache
   */
  public function mergeMailchimpMember($member) {
    // Find ID in table.
    $bao = new CRM_Mailchimpsync_BAO_MailchimpsyncCache();
    $bao->mailchimp_member_id = $member['id'];
    $bao->mailchimp_list_id = $this->mailchimp_list_id;
    if (!$bao->find(1)) {
      // New person.
      $bao->mailchimp_email = $member['email_address'];
    }

    $bao->sync_status = 'todo';
    $bao->mailchimp_status = $member['status'];
    $bao->mailchimp_updated = date('YmdHis', strtotime($member['last_changed']));

    // Create JSON data from Mailchimp.
    $data = [
      'first_name' => $member['merge_fields']['FNAME'] ?? NULL,
      'last_name'  => $member['merge_fields']['LNAME'] ?? NULL,
      'interests'  => $member['interests'] ?? [],
    ];
    // Nb. while I prefer JSON for readability, PHP's serialize is a *lot*
    // faster, which will count on a big list.
    $bao->mailchimp_data = serialize($data);
    // Q. some way here to address the 2 way interests sync ? @todo

    // Update
    $bao->save();

    return $bao;
  }

  /**
   * Remove invalid CiviCRM data.
   *
   * e.g. if a contact is deleted (including a merge).
   *
   * @return array with keys:
   * - updated: int Number of affected rows.
   * - deleted: int number of cache records deleted.
   */
  public function removeInvalidContactIds() {

    $sql_params = [1 => [$this->mailchimp_list_id, 'String']];

    // Remove CiviCRM contact IDs that no longer point to live contacts.
    $sql = "
      UPDATE civicrm_mailchimpsync_cache mc
        LEFT JOIN civicrm_contact cc ON mc.civicrm_contact_id = cc.id AND cc.is_deleted = 0
         SET civicrm_contact_id = NULL, civicrm_groups = NULL, sync_status = 'todo'
       WHERE mc.civicrm_contact_id IS NOT NULL
             AND cc.id IS NULL
             AND mc.mailchimp_list_id = %1;";

    $dao = CRM_Core_DAO::executeQuery($sql, $sql_params);
    $results = ['updated' => $dao->affectedRows()];

    // This might have resulted in some empty cache records, i.e.
    // No ContacID and no MailchimpID. We can safely delete these.
    $dao = CRM_Core_DAO::executeQuery(
      'DELETE FROM civicrm_mailchimpsync_cache
       WHERE mailchimp_member_id IS NULL
         AND civicrm_contact_id IS NULL
         AND mailchimp_list_id = %1;',
      $sql_params);
    $results['deleted'] = $dao->affectedRows();

    $this->log("removeInvalidContactIds: Removed $results[updated] stale CiviCRM Contact IDs; Deleted $results[deleted] empty rows from mailchimpsync cache table.");
    return $results;
  }
  /**
   * Try various techniques for finding an appropriate CiviCRM Contact ID from
   * the email found at Mailchimp.
   *
   * Note: this duplicates the work of the other populateMissingContactIds* functions
   * but does so just for a single contact and without creating temporary tables
   *
   * @param CRM_Mailchimpsync_BAO_MailchimpsyncCache $cache_id
   *
   * @return array of stats.
   */
  public function populateMissingContactIdsSingle(CRM_Mailchimpsync_BAO_MailchimpsyncCache $cache_entry) {

    $stats = [
      'found_by_single_email'                                => 0,
      'used_first_undeleted_contact_in_group'                => 0,
      'used_first_undeleted_contact_with_group_history'      => 0,
      'used_first_undeleted_contact'                         => 0,
      'remaining'                                            => 1,
    ];

    if ($cache_entry->civicrm_contact_id || empty($cache_entry->mailchimp_email)) {
      // Should not happen.
      return $stats;
    }

    // 1. If we find that the email is owned by a single non-deleted contact, use that.
    $emails = \Civi\Api4\Email::get()
      ->addSelect('contact_id')
      ->addWhere('email', '=',$cache_entry->mailchimp_email)
      ->addWhere('contact_id.is_deleted', '=', FALSE)
      ->setCheckPermissions(FALSE)
      ->execute();
    $uniqueContacts = [];
    foreach ($emails as $_) {
      $uniqueContacts[(int) $_['contact_id']] = 1;
    }
    if (!$uniqueContacts) {
      // We don't have this email - at least not on an undeleted contact.
      return $stats;
    }

    if (count($uniqueContacts) === 1) {
      $cache_entry->civicrm_contact_id = $emails[0]['contact_id'];
      $cache_entry->save();
      $stats['found_by_single_email'] = 1;
      $stats['remaining'] = 0;
      return $stats;
    }

    // The following cases are when there's several contacts found for the email.

    // 2. If we have multiple non-deleted contacts, use the first one by contact id,
    // that is in this group.
    $civicrm_subscription_group_id = (int) $this->getSubscriptionGroup();
    $groupContact = \Civi\Api4\GroupContact::get()
      ->addSelect('contact_id')
      ->addWhere('group_id', '=', $civicrm_subscription_group_id)
      ->addWhere('contact_id', 'IN', array_keys($uniqueContacts))
      ->addWhere('status', '=', 'Added')
      ->setCheckPermissions(FALSE)
      ->addOrderBy('contact_id', 'ASC')
      ->setLimit(1)
      ->execute();
    if ($groupContact->count() === 1) {
      $cache_entry->civicrm_contact_id = $groupContact[0]['contact_id'];
      $cache_entry->save();
      $stats['used_first_undeleted_contact_in_group'] = 1;
      $stats['remaining'] = 0;
      return $stats;
    }

    // First contact with any history in this group.
    $dao = CRM_Core_DAO::executeQuery(
      "SELECT contact_id FROM civicrm_subscription_history h
      WHERE h.contact IN (" . implode(',', array_keys($uniqueContacts)) . ")
      AND h.group_id = $civicrm_subscription_group_id
      ORDER BY contact_id LIMIT 1");
    if ($dao->fetch() && $dao->contact_id) {
      $cache_entry->civicrm_contact_id = $dao->contact_id;
      $cache_entry->save();
      $stats['used_first_undeleted_contact_with_group_history'] = 1;
      $stats['remaining'] = 0;
      return $stats;
    }

    // Ok, just use the lowest contact ID.
    $cache_entry->civicrm_contact_id = array_keys($uniqueContacts)[0];
    $cache_entry->save();
    $stats['used_first_undeleted_contact'] = 1;
    $stats['remaining'] = 0;
    return $stats;
  }
  /**
   * Try various techniques for finding an appropriate CiviCRM Contact ID from
   * the email found at Mailchimp.
   *
   * @return array of stats.
   */
  public function populateMissingContactIds() {

    $stats = [
      'found_by_single_email'                                => 0,
      'used_first_undeleted_contact_in_group'                => 0,
      'used_first_undeleted_contact_with_group_history'      => 0,
      'used_first_undeleted_contact'                         => 0,
      'remaining'                                            => 0,
      'time_found_by_single_email'                           => 'N/A',
      'time_used_first_undeleted_contact_in_group'           => 'N/A',
      'time_used_first_undeleted_contact_with_group_history' => 'N/A',
      'time_used_first_undeleted_contact'                    => 'N/A',
    ];

    // Don't run expensive queries if we don't have to: see what's to do.
    $stats['remaining'] = (int) CRM_Core_DAO::executeQuery(
      'SELECT COUNT(*) FROM civicrm_mailchimpsync_cache WHERE mailchimp_list_id = %1 AND civicrm_contact_id IS NULL',
      [1 => [$this->mailchimp_list_id, 'String']]
    )->fetchValue();

    if ($stats['remaining'] == 0) {
      // Nothing to do.
      return $stats;
    }

    // 1. If we find that the email is owned by a single non-deleted contact, use that.
    $start = microtime(TRUE);
    $stats['found_by_single_email'] = $this->populateMissingContactIdsPhase1();
    $stats['time_found_by_single_email'] = number_format(microtime(TRUE) - $start, 2);
    $stats['remaining'] -= $stats['found_by_single_email'];

    if ($stats['remaining'] > 0) {
      $start = microtime(TRUE);
      $stats['used_first_undeleted_contact_in_group'] = $this->populateMissingContactIdsPhase2();
      $stats['remaining'] -= $stats['used_first_undeleted_contact_in_group'];
      $stats['time_used_first_undeleted_contact_in_group'] = number_format(microtime(TRUE) - $start, 2);
    }

    if ($stats['remaining'] > 0) {
      $start = microtime(TRUE);
      $stats['used_first_undeleted_contact_with_group_history'] = $this->populateMissingContactIdsPhase3();
      $stats['remaining'] -= $stats['used_first_undeleted_contact_with_group_history'];
      $stats['time_used_first_undeleted_contact_with_group_history'] = number_format(microtime(TRUE) - $start, 2);
    }

    if ($stats['remaining'] > 0) {
      $stats['used_first_undeleted_contact'] = $this->populateMissingContactIdsPhase4();
      $stats['remaining'] -= $stats['used_first_undeleted_contact'];
      $stats['time_used_first_undeleted_contact'] = number_format(microtime(TRUE) - $start, 2);
    }

    // Remaining contacts are new to CiviCRM.

    // Drop the temporary table (phases 1, 4)
    CRM_Core_DAO::executeQuery('DROP TEMPORARY TABLE IF EXISTS mcs_undeleted_emails;');
    CRM_Core_DAO::executeQuery('DROP TEMPORARY TABLE mcs_emails_needing_matches;');

    // Create them now.
    return $stats;
  }
  /**
   * 1. If the email we're trying to match only belongs to one (undeleted) contact,
   * use that.
   *
   * @return int
   */
  protected function populateMissingContactIdsPhase1() {

    CRM_Core_DAO::executeQuery(
      'CREATE TEMPORARY TABLE mcs_emails_needing_matches (email VARCHAR(255) PRIMARY KEY)
        SELECT mailchimp_email email FROM civicrm_mailchimpsync_cache mc
        WHERE mc.civicrm_contact_id IS NULL AND mc.mailchimp_list_id = %1 AND mailchimp_email IS NOT NULL;',
      [1 => [$this->mailchimp_list_id, 'String']]);

    // We need a table of emails from Civi that aren't deleted.
    CRM_Core_DAO::executeQuery(
      'CREATE TEMPORARY TABLE mcs_undeleted_emails (
        contact_id INT(10) UNSIGNED,
        email VARCHAR(255),
        KEY (email, contact_id))
      SELECT contact_id, e.email
      FROM civicrm_email e
      INNER JOIN mcs_emails_needing_matches me ON me.email = e.email
      WHERE EXISTS (SELECT 1 FROM civicrm_contact WHERE id = contact_id AND is_deleted = 0);');

    // Create table of emails that only belong to one contact.
    CRM_Core_DAO::executeQuery(
      'CREATE TEMPORARY TABLE mcs_phase_1 (
        email VARCHAR(255) PRIMARY KEY,
        contact_id INT(10) UNSIGNED
      )
      SELECT email, MIN(contact_id) contact_id
      FROM mcs_undeleted_emails ue
      GROUP BY email
      HAVING COUNT(DISTINCT contact_id) = 1;');

    // Now update our main table where there's only one.
    $dao = CRM_Core_DAO::executeQuery(
      'UPDATE civicrm_mailchimpsync_cache mc
              INNER JOIN mcs_phase_1 ue1 ON ue1.email = mc.mailchimp_email
       SET mc.civicrm_contact_id = ue1.contact_id
       WHERE mc.civicrm_contact_id IS NULL AND mc.mailchimp_list_id = %1;',
      [1 => [$this->mailchimp_list_id, 'String']]);

    $updated = $dao->affectedRows();

    // Drop temporary table that was just for this phase
    CRM_Core_DAO::executeQuery('DROP TEMPORARY TABLE mcs_phase_1;');

    return $updated;
  }

  /**
   * 2. Next, use the first (lowest contact ID) that owns the email that is
   * Added to the group.  This runs really fast (<1s), even with a lot (50k)
   * of contacts.
   *
   * @return int
   */
  protected function populateMissingContactIdsPhase2() {
    $civicrm_subscription_group_id = $this->getSubscriptionGroup();
    $sql = "
      UPDATE civicrm_mailchimpsync_cache mc
        INNER JOIN (
          SELECT e.email, MIN(e.contact_id) contact_id
            FROM civicrm_email e
            INNER JOIN civicrm_contact c1 ON e.contact_id = c1.id AND c1.is_deleted = 0
            INNER JOIN civicrm_group_contact g
                      ON e.contact_id = g.contact_id
                      AND g.group_id = $civicrm_subscription_group_id
                      AND g.status = 'Added'
          GROUP BY e.email
        ) c ON c.email = mc.mailchimp_email
        SET mc.civicrm_contact_id = c.contact_id
        WHERE mc.civicrm_contact_id IS NULL
              AND c.contact_id IS NOT NULL
              AND mc.mailchimp_list_id = %1
      ";
    $dao = CRM_Core_DAO::executeQuery($sql, [1 => [$this->mailchimp_list_id, 'String']]);
    return $dao->affectedRows();
  }
  /**
   * 3. Next, use the first (lowest contact ID) that owns the email that has any group history.
   * This is also fast.
   *
   * @return int
   */
  protected function populateMissingContactIdsPhase3() {
    $civicrm_subscription_group_id = $this->getSubscriptionGroup();
    $sql = "
      UPDATE civicrm_mailchimpsync_cache mc
        INNER JOIN (
          SELECT e.email, MIN(e.contact_id) contact_id
            FROM civicrm_email e
            INNER JOIN civicrm_contact c1 ON e.contact_id = c1.id AND NOT c1.is_deleted
            INNER JOIN civicrm_subscription_history h
                      ON e.contact_id = h.contact_id
                      AND h.group_id = $civicrm_subscription_group_id
          GROUP BY e.email
        ) c ON c.email = mc.mailchimp_email
        SET mc.civicrm_contact_id = c.contact_id
        WHERE mc.civicrm_contact_id IS NULL
              AND c.contact_id IS NOT NULL
              AND mc.mailchimp_list_id = %1
      ";
    $dao = CRM_Core_DAO::executeQuery($sql, [1 => [$this->mailchimp_list_id, 'String']]);
    return $dao->affectedRows();
  }
  protected function populateMissingContactIdsPhase4() {

    // Create table with the first contact owner of each email.
    CRM_Core_DAO::executeQuery(
      'CREATE TEMPORARY TABLE mcs_emails_phase_4 (
        email VARCHAR(255) PRIMARY KEY,
        contact_id INT(10) UNSIGNED
      )
      SELECT email, MIN(contact_id) contact_id
      FROM mcs_undeleted_emails ue
      GROUP BY email;');

    // Now update our main table where there's only one.
    $dao = CRM_Core_DAO::executeQuery(
      'UPDATE civicrm_mailchimpsync_cache mc
              INNER JOIN mcs_emails_phase_4 ue1 ON ue1.email = mc.mailchimp_email
       SET mc.civicrm_contact_id = ue1.contact_id
       WHERE mc.civicrm_contact_id IS NULL AND mc.mailchimp_list_id = %1;',
      [1 => [$this->mailchimp_list_id, 'String']]);

    $updated = $dao->affectedRows();

    // Drop temporary tables.
    CRM_Core_DAO::executeQuery('DROP TEMPORARY TABLE mcs_emails_phase_4;');

    return $updated;
  }

  /**
   * Create contacts found at Mailchimp but not in CiviCRM.
   *
   * Call this after calling populateMissingContactIds()
   *
   * Nb. if someone comes in from Mailchimp who is not subscribed there's no
   * point us adding them in, however we leave them in the cache table.
   *
   * @param null|int time in seconds to spend.
   * @return int No. contacts created.
   */
  public function createNewContactsFromMailchimp($remaining_time=NULL) {

    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => 'busy',
      'andLog' => 'createNewContactsFromMailchimp: beginning.'
    ]);
    $total = 0;
    $dao = CRM_Core_DAO::executeQuery(
      'SELECT * FROM civicrm_mailchimpsync_cache WHERE mailchimp_list_id = %1 AND civicrm_contact_id IS NULL',
      [ 1 => [$this->mailchimp_list_id, 'String'] ]
    );
    $stop_time = time() + ($remaining_time ?? 60*60*24*7);

    while ($dao->fetch()) {

      if ($this->createNewContact($dao)) {
        $total++;
      }

      if (time() >= $stop_time) {
        // Time's up, but we're not finished.
        $this->updateLock([
          'for'    => 'fetchAndReconcile',
          'to'     => 'readyToCreateNewContactsFromMailchimp',
          'andLog' => "createNewContactsFromMailchimp: Added $total new contacts found in Mailchimp but not CiviCRM but out of time - more to do.",
        ]);
        return $total;
      }
    }
    // Completed!
    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => 'readyToAddCiviOnly',
      'andLog' => "createNewContactsFromMailchimp: Added $total new contacts found in Mailchimp but not CiviCRM. No more to do.",
    ]);
    return $total;
  }
  /**
   * Create new contact from the data given.
   *
   * @param CRM_Core_DAO $dao - this could be a
   * CRM_Mailchimpsync_BAO_MailchimpsyncCache or a custom SQL DAO;
   * as long as it has these fields:
   *
   * - id
   * - mailchimp_status
   * - mailchimp_email
   * - mailchimp_data
   *
   * @return FALSE|Integer Contact ID created.
   */
  public function createNewContact($dao) {
    $id = (int) $dao->id;

    if (!in_array($dao->mailchimp_status, ['pending', 'subscribed'])) {
      // Only import contacts that are subscribed.
      return FALSE;
    }
    // Create contact.
    $params = [
      'contact_type' => 'Individual',
      'email'        => $dao->mailchimp_email,
      'source'       => 'mailchimpsync',
    ];

    // If we have their name, use it to create contact.
    $mailchimp_data = unserialize($dao->mailchimp_data);
    foreach (['first_name', 'last_name'] as $field) {
      if (!empty($mailchimp_data[$field])) {
        $params[$field] = $mailchimp_data[$field];
      }
    }

    $contact_id = (int) civicrm_api3('Contact', 'create', $params)['id'];
    CRM_Core_DAO::executeQuery("UPDATE civicrm_mailchimpsync_cache SET civicrm_contact_id = $contact_id WHERE id = $id");

    return $contact_id;
  }
  /**
   *
   * If there are any contacts Added to the subscription group in CiviCRM, but
   * not known to Mailchimp, add them to the cache table.
   *
   * @param array $params
   */
  public function addCiviOnly() {
    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => 'busy',
      'andLog' => "addCiviOnly: Adding contacts found in CiviCRM but not in Mailchimp.",
    ]);
    $civicrm_subscription_group_id = $this->getSubscriptionGroup();

    $sql = "
      INSERT INTO civicrm_mailchimpsync_cache (mailchimp_list_id, civicrm_contact_id, sync_status)
        SELECT %1 mailchimp_list_id, contact_id, 'todo' sync_status
      FROM civicrm_group_contact gc
      INNER JOIN civicrm_contact c ON gc.contact_id = c.id AND c.is_deleted = 0
      WHERE gc.group_id = $civicrm_subscription_group_id
            AND gc.status = 'Added'
            AND NOT EXISTS (
              SELECT id FROM civicrm_mailchimpsync_cache mc2
              WHERE mc2.mailchimp_list_id = %1 AND civicrm_contact_id = gc.contact_id
          )
    ";
    $dao = CRM_Core_DAO::executeQuery($sql, [
      1 => [$this->mailchimp_list_id, 'String']
    ]);
    $total = $dao->affectedRows();
    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => 'readyToCleanUpDuplicates',
      'andLog' => "addCiviOnly: Added $total contacts found in CiviCRM but not in Mailchimp.",
    ]);
    return $total;
  }

  /**
   *
   * This is an awkward case:
   * - Contact X is in Civi but not in Mailchimp, so gets added to the cache
   *   without mailchimp_member_id
   *
   * - Contact X's email address gets added to Mailchimp. This causes a new
   *   cache record to be created since the first one does not match on member
   *   id.
   *
   * - We then end up with 2 records for the same contact in the cache table.
   *
   * This procedure will delete the CiviCRM one from the cache, since it's no longer needed.
   *
   */
  public function cleanUpDuplicatesInCache() {
    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => 'busy',
      'andLog' => "cleanUpDuplicatesInCache: starting",
    ]);
    $sql = "
      SELECT c1.id
      FROM civicrm_mailchimpsync_cache c1
      WHERE c1.mailchimp_list_id = %1
        AND c1.mailchimp_member_id IS NULL
        AND EXISTS (
          SELECT id
          FROM civicrm_mailchimpsync_cache c2
          WHERE c2.mailchimp_list_id = %1
            AND c1.civicrm_contact_id = c2.civicrm_contact_id
            AND c2.id != c1.id
            AND c2.mailchimp_member_id IS NOT NULL
        )
    ";
    $ids = CRM_Core_DAO::executeQuery($sql, [
      1 => [$this->mailchimp_list_id, 'String']
    ])->fetchMap('id', 'id');
    if ($ids) {
      CRM_Core_DAO::executeQuery("DELETE from civicrm_mailchimpsync_cache WHERE id IN ("
        . implode(', ', $ids)
        . ")");
    }
    $total = count($ids);
    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => 'readyToCheckForGroupChanges',
      'andLog' => "cleanUpDuplicatesInCache: removed $total duplicates.",
    ]);
    return $total;
  }

  /**
   * We need to mark 'todo' any rows where any group related to this list is changed.
   */
  public function identifyGroupContactChanges($since, $next) {
    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => 'busy',
      'andLog' => "identifyGroupContactChanges: Copying CiviCRM's subscription group update dates "
      . ($since
        ? "since " . date('j M Y H:i:s', $since)
        : ''),
    ]);

    if ($since) {
      $group_ids = implode(',', $this->getGroupIds());
      $sql = "
          UPDATE civicrm_mailchimpsync_cache c
          SET sync_status = 'todo'
          WHERE mailchimp_list_id = %1
            AND sync_status != 'todo'
            AND EXISTS (
              SELECT contact_id
              FROM civicrm_subscription_history h
              WHERE group_id IN ($group_ids)
                    AND h.contact_id = c.civicrm_contact_id
                    AND h.date >= %2
           )";
      $dao = CRM_Core_DAO::executeQuery($sql, [
          1 => [$this->getListId(), 'String'],
          2 => [date('YmdHis', $since), 'String'],
        ]);
    }
    else {
      // Without a 'since' limit, everything is todo, except
      // contacts that don't exist in CiviCRM and shouldn't be created because
      // they're not subscribed in Mailchimp.
      $dao = CRM_Core_DAO::executeQuery("UPDATE civicrm_mailchimpsync_cache
        SET sync_status = 'todo' WHERE mailchimp_list_id = %1 AND sync_status != 'todo' AND civicrm_contact_id IS NOT NULL",
        [1 => [$this->getListId(), 'String']]);
    }
    $affected = $dao->affectedRows();

    // Update cache of groups.
    CRM_Mailchimpsync_BAO_MailchimpsyncCache::updateCiviCRMGroups(
      ['list_id' => $this->mailchimp_list_id]);

    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => $next,
      'andLog' => "identifyGroupContactChanges: complete ($affected records added to todo list).",
    ]);
  }
  /**
   * A data sync allows other extensions to provide data for Mailchimp.
   *
   * Extensions implementing hook_mailchimpsync_data_updates_check 
   * should do what they need to ascertain what the data should be, and if it has
   * changed. Data should be stored in the civicrm_data field.
   * If it needs changing the sync_status should be set to 'todo'
   *
   * The civicrm_data field format should be like this:
   * [
   *   'your_identifier' => [
   *     'mailchimp_updates' => [],
   *     'tag_updates' => [],
   *     'other_for_your_use' => ...
   *   ]
   * ]
   *
   * All extensions' 'mailchimp_updates' will be merged into the main
   * member POST/PATCH/PUT update call.
   *
   * And the 'tag_updates' contents becomes a tags update.
   *
   * Both are optional.
   *
   * Extensions can use other fields to store things like hashes of the last data
   * so they can tell if an update is needed.
   *
   * If your process is likely to be slow (it probably is if your list is
   * significant) then you will need to handle stopping and starting by storing
   * a processing offset. The audience's config would be the right place for
   * this, but needs more thought.
   *
   * Nb. this is here as a separate process so that this process can include
   * things in they sync that might not have been included otherwise. e.g. if
   * the subscription status has not changed, a contact would not be included,
   * yet their data might have changed.
   *
   * @param int $remaining_time
   * @param int $relevant_since
   */
  public function checkForDataUpdates($remaining_time, $relevant_since=NULL, $single_cache_item_id = NULL) {

    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => 'busy',
      'andLog' => "checkForDataUpdates: calling hooks",
    ]);

    $complete = TRUE; // xxx @todo we should be stop-and-startable not a monolytic process.
    $stop_time = time() + ($remaining_time ?? 60*60*24*7);

    $this->dataUpdates();

    if ($complete) {
      $this->updateLock([
        'for'    => 'fetchAndReconcile',
        'to'     => 'readyToReconcileQueue',
        'andLog' => "checkForDataUpdates: completed.",
      ]);
    }
    else {
      $this->updateLock([
        'for'    => 'fetchAndReconcile',
        'to'     => 'readyToCheckForDataUpdates',
        'andLog' => "checkForDataUpdates: incomplete.",
      ]);
    }
  }
  /**
   * Run the hooks to let extensions update data on subscribed contacts.
   *
   * This will call the following (symfony) hooks:
   *
   * - mailchimpsync_data_updates_check_pre
   * - mailchimpsync_data_updates_check - called for every cache entry processed.
   * - mailchimpsync_data_updates_check_post
   *
   * @param NULL|int $single_cache_item_id
   *    If an integer, we only inspect that on cache entry, otherwise we loop
   *    all the relevant (see comments below) ones.
   *
   * @todo make this stop-startable.
   */
  public function dataUpdates($single_cache_item_id = NULL) {

    // Call a hook before doing the loop. This gives extensions the chance to
    // do some bulk calculation and stash the result somewhere for faster
    // lookup later. $pre_data is passed into the per-cache-item call. It's
    // probably safest for extensions to store their data under a key with
    // their name to avoid potential collsions with others.
    $pre_data = [];

    // Now loop all cache items.
    if ($single_cache_item_id) {
      $cache_ids = [$single_cache_item_id];
    }
    else {
      // All cache entries for this audience that are subscribed
      //
      // We try to only update mailchimp where the contact is subscribed, or is
      // about to be subscribed. The SQL below is a superset of those
      // because reconcileSubscriptionGroup, which figures out what the status should be,
      // has not yet run.
      //
      // We run for all who were - at last reconcile - subscribed/pending at mailchimp.
      // PLUS any that we are processing this run (sync_status 'todo').
      //
      // This will include anyone who is unsubscribed/unknown at Mailchimp that
      // we're about to subscribe, but it will also include those that we're
      // about to unsubscribe, and any that were marked todo by some other
      // (e.g. manual) process who might not have any updates required at all.
      //
      // Generating data for these extra records is an overhead, but is thought to be
      // a smaller overhead than trying to work the data updates in at a later stage.
      //
      // However, note that implementations *should* check their generated data
      // against their own hash of that data from the last update, to avoid
      // issuing update requests that do nothing. As data is one-way, we do NOT
      // cater for the case that someone messed up the data at mailchimp; we expect
      // that data we set to X will remain X until we change it.
      //
      $params = [1 => [$this->getListId(), 'String']];
      $cache_ids = CRM_Core_DAO::executeQuery(
        "SELECT c.id
        FROM civicrm_mailchimpsync_cache c
        WHERE c.mailchimp_list_id = %1
          AND (
            c.mailchimp_status IN ('subscribed', 'pending')
            OR c.sync_status = 'todo'
          )
        ORDER BY id
        ",
        $params
      )->fetchMap('id', 'id');
    }

    if (!$cache_ids) {
      // Nothing to do!
      return;
    }

    $dummy = NULL;
    CRM_Utils_Hook::singleton()->invoke(
      ['audience', 'cache_entry_ids', 'pre_data'],
      $this, $cache_ids, $pre_data,
      $dummy, $dummy, $dummy,
      'mailchimpsync_data_updates_check_pre');

    $done = 0;
    foreach ($cache_ids as $cache_id) {
      // if ($stop_time && (time() > $stop_time)) {
      //   // Time to stop.
      //   break;
      // }
      $cache_entry = new CRM_Mailchimpsync_BAO_MailchimpsyncCache();
      $cache_entry->id = $cache_id;
      $cache_entry->find(TRUE);

      // Call hook_mailchimpsync_data_updates_check
      // to optionally update this cache item.
      // (the hook should NOT save the cache entry)
      $needs_saving = FALSE;
      CRM_Utils_Hook::singleton()->invoke(
        ['audience', 'cache_entry', 'pre_data', 'needs_saving'],
        $this, $cache_entry, $pre_data, $needs_saving,
        $dummy, $dummy,
        'mailchimpsync_data_updates_check');

      if ($needs_saving) {
        $cache_entry->save();
      }
    }

    // Offer hook for cleanup. (e.g. remove temporary tables or such - probably
    // not needed much.)
    CRM_Utils_Hook::singleton()->invoke(
      ['audience', 'cache_entry_ids', 'pre_data'],
      $this, $cache_ids, $pre_data,
      $dummy, $dummy, $dummy,
      'mailchimpsync_data_updates_check_post');
  }
  // The following methods deal with the 'reconciliation' phase
  /**
   * Loop 'todo' entries and reconcile them.
   *
   * @param int $max_time If >0 then stop if we've been running longer than
   * this many seconds. This is useful for http driven cron, for exmaple.
   * @param int $relevant_since
   * @param bool $with_data
   */
  public function reconcileQueueProcess(int $max_time=0, $relevant_since = NULL, $with_data = FALSE) {
    $this->updateLock([
      'for'    => 'fetchAndReconcile',
      'to'     => 'busy',
      'andLog' => 'reconcileQueueProcess beginning',
    ]);
    $stop_time = ($max_time > 0) ? time() + $max_time : FALSE;

    $params = [1 => [$this->getListId(), 'String']];
    $cache_id_to_subs = CRM_Core_DAO::executeQuery(
      "SELECT c.id
        FROM civicrm_mailchimpsync_cache c
       WHERE c.mailchimp_list_id = %1
             AND sync_status = 'todo'
      ",
      $params
    )->fetchMap('id', 'id');

    $done = 0;
    foreach ($cache_id_to_subs as $cache_id) {
      if ($stop_time && (time() > $stop_time)) {
        // Time to stop.
        break;
      }
      $dao = new CRM_Mailchimpsync_BAO_MailchimpsyncCache();
      $dao->id = $cache_id;
      $dao->find(TRUE);
      $this->reconcileQueueItem($dao, $with_data);
      $done++;
    }

    $stats = ['done' => $done, 'count' => count($cache_id_to_subs)];

    // For logs information, look up number of updates pending.
    $mailchimp_updates = (int) CRM_Core_DAO::singleValueQuery(
      'SELECT COUNT(*)
      FROM civicrm_mailchimpsync_update u
      INNER JOIN civicrm_mailchimpsync_cache c ON u.mailchimpsync_cache_id = c.id AND c.mailchimp_list_id = %1
      WHERE completed = 0;',
      [1 => [$this->mailchimp_list_id, 'String']]
    );

    if ($stats['done'] == $stats['count']) {
      $this->updateLock([
        'for'    => 'fetchAndReconcile',
        'to'     => 'readyToSubmitUpdates',
        'andLog' => "reconcileQueueProcess: Completed reconciliation of $stats[done] contacts. (Need to submit $mailchimp_updates updates to Mailchimp)",
        'andAlso' => function(&$c) { unset($c['fetch']['since']); }
      ]);
    }
    else {
      $this->updateLock([
        'for'    => 'fetchAndReconcile',
        'to'     => 'readyToReconcileQueue', // reset for next time.
        'andLog' => "reconcileQueueProcess: Reconciled $stats[done], " . ($stats['count'] - $stats[done]) . " remaining but ran out of time. So far, $mailchimp_updates updates to Mailchimp in the queue.",
      ]);
    }
    return $stats;
  }
  /**
   * Reconcile a single item from the cache table.
   *
   * This will result in the item having status 'ok', or 'live' if mailchimp updates are queued.
   *
   * @param CRM_Mailchimpsync_DAO_MailchimpsyncCache $dao
   * @param bool $with_data
   */
  public function reconcileQueueItem(CRM_Mailchimpsync_BAO_MailchimpsyncCache $cache_entry, $with_data=FALSE) {

    $mailchimp_updates = [];
    $tags = [];
    try {
      $subs = $this->parseSubs($cache_entry->mailchimp_updated, $cache_entry->civicrm_groups);
      $final_state_is_subscribed = $this->reconcileSubscriptionGroup($mailchimp_updates, $cache_entry, $subs);

      if ($final_state_is_subscribed) {
        // This is not an unsubscribe request, so process other data, too.

        $this->reconcileInterestGroups($mailchimp_updates, $cache_entry, $subs);

        if ($with_data) {
          $this->reconcileExtraData($cache_entry, $mailchimp_updates, $tags);
        }
        // Note:
        // Originally imagined this is where we would call a hook to change the
        // updates, however, that would not give the data-sync-ing hooks the
        // opportunity to add cache entries into the sync.
      }

      if ($mailchimp_updates || $tags) {

        $cache_entry->sync_status = 'live';
        $cache_entry->save();

        if ($mailchimp_updates) {
          // Queue a mailchimp member update.
          $update = new CRM_Mailchimpsync_BAO_MailchimpsyncUpdate();
          $update->mailchimpsync_cache_id = $cache_entry->id;
          $update->data = json_encode($mailchimp_updates);
          $update->save();
        }
        if ($tags) {
          // Queue a mailchimp member tags update.
          $update = new CRM_Mailchimpsync_BAO_MailchimpsyncUpdate();
          $update->mailchimpsync_cache_id = $cache_entry->id;
          $update->data = json_encode(['tags' => $tags]);
          $update->save();
        }
      }
      else {
        // Updates were not needed or have all been done on the CiviCRM side.
        $cache_entry->sync_status = 'ok';
        $cache_entry->save();
      }
    }
    catch (CRM_Mailchimpsync_CannotSyncException $e) {
      // This contact cannot be sync'ed
      Civi::log()->warning("Marking contact $cache_entry->civicrm_contact_id as failed Mailchimpsync status: " . $e->getMessage());
      $cache_entry->sync_status = 'fail';
      $cache_entry->save();
    }

  }

  /**
   * Ensure we have CiviCRM's subscription group membership in sync with Mailchimp's.
   *
   * @param &array $mailchimp_updates
   * @param CRM_Mailchimpsync_BAO_MailchimpsyncCache $cache_entry
   * @param array $subs
   *
   * @return bool TRUE if the person's final state should be subscribed
   */
  public function reconcileSubscriptionGroup(&$mailchimp_updates, CRM_Mailchimpsync_BAO_MailchimpsyncCache $cache_entry, $subs) {

    $civicrm_subscription = $subs[$this->config['subscriptionGroup']] ?? NULL;

    if ($civicrm_subscription['mostRecent'] === 'Mailchimp') {
      // Exists in Mailchimp and Mailchimp has been updated since CiviCRM was,
      // at least in terms of the subscription group, or the contact has no group
      // subscription history.

      if ($civicrm_subscription['status'] === 'Added') {
        // M:? C:Added

        if ($cache_entry->isSubscribedAtMailchimp()) {
          // M:subscribed, C:Added
          // Subscribed (could be Pending at MC) at both ends.
          // No subscription group level changes needed.
          return TRUE;
        }
        else {
          // M:!subscribed, C:Added
          // Mailchimp has unsubscribed/cleaned/archived this contact
          // (or, converted it to transactional - not sure if that happens)
          // So we need to remove this contact from the subscription group.
          // Issue #9: UNLESS the CiviCRM contact has another mailchimp cache
          // record that is subscribed.
          $cache_entry->unsubscribeInCiviCRM($this);
          return FALSE;
        }
      }
      else {
        // M:?, C:!Added
        // Removed, Deleted, or no subscription history in CiviCRM
        if ($cache_entry->isSubscribedAtMailchimp()) {
          // M:subscribed, C:!Added
          $cache_entry->subscribeInCiviCRM($this);
          return TRUE;
        }
        else {
          // M:!subscribed, C:!Added
          // Not subscribed at MC and not in CiviCRM's either: subscription is in sync.
          return FALSE;
        }
      }
    }
    else {
      // Either does not exist in Mailchimp yet, or does but CiviCRM is more up-to-date
      // in terms of its subscription group.
      if ($civicrm_subscription['status'] === 'Added') {
        if ($cache_entry->isSubscribedAtMailchimp()) {
          // C:Added, M:subscribed
          // in sync.
          return TRUE;
        }
        else {
          // C:Added, M:?
          switch ($cache_entry->mailchimp_status) {
          case null:
            // Find best email to use to subscribe this contact.
            $mailchimp_updates['email_address'] = $this->getBestEmailForNewContact($cache_entry->civicrm_contact_id);
            $mailchimp_updates['status'] = 'subscribed';
            return TRUE;

          case 'unsubscribed':
          case 'archived':
          case 'transactional':
            if ($cache_entry->mailchimpEmailIsOnHoldInCivi()) {
              throw new CRM_Mailchimpsync_CannotSyncException("Contact's email adderss is on hold in CiviCRM; refusing to resubscribe at Mailchimp.");
            }
            // @see https://github.com/artfulrobot/mailchimpsync/issues/10
            // Before we set the status to subscribed, we have to check if
            // there are any unsubmitted updates setting the subscription to
            // 'pending'. If so we need to load that update and abort it.
            $existing_update = new CRM_Mailchimpsync_BAO_MailchimpsyncUpdate();
            $existing_update->mailchimpsync_cache_id = $cache_entry->id;
            $existing_update->mailchimpsync_batch_id = 'null';
            $existing_update->orderBy('id'); // Process older first, this way we prefer newer details.

            $require_pending_status = FALSE;
            if ($existing_update->find()) {
              $updates_to_abort = $existing_update->fetchMap('id', 'id');
              foreach ($updates_to_abort as $update_id) {
                $existing_update = new CRM_Mailchimpsync_BAO_MailchimpsyncUpdate();
                $existing_update->id = $update_id;
                $existing_update->find(TRUE);

                // Merge any data from the old update not yet present in our update.
                $old_update = json_decode($existing_update->data, TRUE);
                $mailchimp_updates = array_merge($old_update, $mailchimp_updates);
                // Check 'status' value
                $require_pending_status |= (($old_update['status'] ?? '') === 'pending');
                // Abort old update.
                $existing_update->completed = 1;
                $existing_update->error_response = 'Update aborted as superseded by another update before this was submitted.';
                $existing_update->save();
              }
            }
            // Force status.
            $mailchimp_updates['status'] = $require_pending_status ? 'pending' : 'subscribed';
            return TRUE;
            break;

          case 'cleaned':
          default:
          // We will not be able to subscribe this person.
          throw new CRM_Mailchimpsync_CannotSyncException("Contact 'cleaned' by mailchimp; cannot resubscribe.");
            break;
          }
        }
      }
      else {
        // Not subscribed in CiviCRM.
        if ($cache_entry->isSubscribedAtMailchimp()) {
          // Is subscribed at Mailchimp but should not be.
          $mailchimp_updates['status'] = 'unsubscribed';
          return FALSE;
        }
        else {
          // Not subscribed at Mailchimp or Civi, so already in-sync.
          return FALSE;
        }
      }
    }
  }

  /**
   * Interest groups
   *
   * @param &array $mailchimp_updates
   * @param CRM_Mailchimpsync_BAO_MailchimpsyncCache $cache_entry
   * @param array $subs
   */
  public function reconcileInterestGroups(&$mailchimp_updates, CRM_Mailchimpsync_BAO_MailchimpsyncCache $cache_entry, $subs) {
    // each interest group...

    $mailchimp_data = unserialize($cache_entry->mailchimp_data);
    foreach ($this->config['interests'] ?? [] as $interest_id => $group_id) {
      if (isset($mailchimp_data['interests'][$interest_id])) {
        $mailchimp_status = (bool) $mailchimp_data['interests'][$interest_id];
        $civicrm_status = (($subs[$group_id]['status'] ?? '') === 'Added');

        if ($mailchimp_status !== $civicrm_status) {
          // There are differences.

          if (($subs[$group_id]['mostRecent'] ?? 'Mailchimp') === 'Mailchimp' ) {
            // Mailchimp was most recently updated. This also includes the case
            // when CiviCRM has no group subscription history for this group
            // and therefore we say Mailchimp is authoritative.
            $contacts = [$cache_entry->civicrm_contact_id];
            if ($mailchimp_status) {
              CRM_Contact_BAO_GroupContact::addContactsToGroup($contacts, $group_id, 'MCsync');
            }
            else {
              CRM_Contact_BAO_GroupContact::removeContactsFromGroup($contacts, $group_id, 'MCsync', 'Removed');
            }
          }
          else {
            // CiviCRM was most recently updated, we will update Mailchimp.
            $mailchimp_updates['interests'][$interest_id] = $civicrm_status ? TRUE : FALSE;
          }
        }
      }
      else {
        // Hmmm. Mailchimp is not returning any data for this.
        // e.g. Interest has been deleted?
        // Clearly we cannot update anything.
        continue;
      }
    }
  }

  /**
   * Merge in data.
   *
   * example civicrm_data:
   * {
   *   some_data_sync_ext : {
   *     mailchimp_updates: {...},  (optional)
   *     tag_updates: {...},        (optional)
   *     something_meta: <mixed>,
   *     ...
   *   },
   *   ...
   * }
   *
   * Each key of the 'civicrm_data' array is inspected and all the mailchimp_updates
   * are merged into the $mailchimp_updates param. Likewise the $tags param.
   *
   * These are then *removed* from the civicrm_data so they don't keep generating updates.
   *
   * The metadata (anything that is not under 'mailchimp_updates' or 'tag_updates') is preserved.
   *
   * @param CRM_Mailchimpsync_BAO_MailchimpsyncCache $cache_entry
   * @param array &$mailchimp_updates
   * @param array &$tags
   */
  public function reconcileExtraData($cache_entry, &$mailchimp_updates, &$tags) {

    if ($cache_entry->civicrm_data) {
      $data = unserialize($cache_entry->civicrm_data);
      $changed = FALSE;
      if ($data && is_array($data)) {
        // Each extension adds its own array of changes, keyed by its name.
        // We don't care about its name here, we just want to merge its data.
        foreach ($data as $key => $d) {
          if (!empty($d['mailchimp_updates'])) {
            // We also disallow extensions setting 'status'
            unset($d['mailchimp_updates']['status']);
            // We merge our data on top of the extension's so that ours takes precidence.
            $mailchimp_updates = array_replace_recursive($d['mailchimp_updates'], $mailchimp_updates);
            unset($data[$key]['mailchimp_updates']);
            $changed = TRUE;
          }
          if (!empty($d['tag_updates'])) {
            // Just concatenate the tag updates.
            $tags = array_merge($d['tag_updates']);
            unset($data[$key]['tag_updates']);
            $changed = TRUE;
          }
        }

        if ($changed) {
          // Store the changes.
          $cache_entry->civicrm_data = serialize($data);
        }
      }
    }

  }
  // The following methods deal with the batching phase.
  /**
   *
   * Look for updates, submit up to 1000 at a time to the API.
   *
   * @return int number of requests sent.
   */
  public function submitUpdatesBatches($remaining_time) {
    $stop_time = time() + $remaining_time;
    $this->updateLock([
      'for' => 'fetchAndReconcile',
      'to' => 'busy',
      'andLog' => 'submitUpdatesBatches: Beginning to batch up any updates...',
    ]);

    $grand_total = 0;
    $batches = 0;
    do {
      $count = $this->submitBatch();
      $grand_total += $count;
      if ($count) {
        $batches++;
      }
    } while (time() < $stop_time && $count > 0);

    if ($count == 0) {
      $this->updateLock([
        'for'    => 'fetchAndReconcile',
        'to'     => 'readyToFetch',
        'andLog' => "submitUpdatesBatches: Completed submission of $batches batches with $grand_total updates...",
      ]);
      return ['batches' => $batches, 'total_operations' => $grand_total, 'is_complete' => 1];
    }
    else {
      $this->updateLock([
        'for'    => 'fetchAndReconcile',
        'to'     => 'readyToSubmitUpdates',
        'andLog' => "submitUpdatesBatches: Out of time. $batches batches with $grand_total updates were submitted.",
      ]);
      return ['batches' => $batches, 'total_operations' => $grand_total, 'is_complete' => 0];
    }
  }

  /**
   *
   * Look for updates, submit up to 1000 at a time to the API.
   *
   * @return int number of requests sent.
   */
  public function submitBatch() {
    $sql = "SELECT up.*, cache.mailchimp_email
      FROM civicrm_mailchimpsync_update up
        INNER JOIN civicrm_mailchimpsync_cache cache
          ON up.mailchimpsync_cache_id = cache.id
             AND cache.sync_status = 'live'
             AND cache.mailchimp_list_id = %1
      WHERE up.mailchimpsync_batch_id IS NULL AND up.completed = 0
      LIMIT 1000";
    $params = [1 => [$this->mailchimp_list_id, 'String']];
    $dao = CRM_Core_DAO::executeQuery($sql, $params);

    $requests = [];
    $url_stub = "/lists/$this->mailchimp_list_id/members";
    $api = $this->getMailchimpApi();

    // Create requests
    while ($dao->fetch()) {
      // Updates are stored with a degree of normalisation.
      // We need to add in details and construct the URL.
      $id = $dao->id;
      $data = json_decode($dao->data, TRUE);
      $requests[$id] = [
        'operation_id' => 'mailchimpsync_' . $id,
      ];
      // Skip merge field validation
      // https://lab.civicrm.org/extensions/mailchimpsync/issues/5
      $data['skip_merge_validation'] = TRUE;

      if (!isset($data['tags'])) {
        // Normal update.

        if (!$dao->mailchimp_email) {
          // Contact was not found on Mailchimp. The $data should already
          // include the email address.
          $requests[$id] += [
            'method'       => 'POST',
            'path'         => $url_stub,
          ];
        }
        else {
          // Contact is already known at mailchimp, so we use mailchimp's email.
          $data['email_address'] = $dao->mailchimp_email;
          $requests[$id] += [
            'method'       => 'PUT',
            'path'         => "$url_stub/" . $api->getMailchimpMemberIdFromEmail($dao->mailchimp_email),
          ];
        }
        $requests[$id]['body'] = json_encode($data);
      }
      else {
        // Tags update.
        // only work for existing contacts.
        // Hopefully this should be OK: in the case of a new contact, because
        // we queue the create contact first, the record should exist for us to
        // update.
        // There could be a case when the last batch entry is a create member
        // and the first entry of a different batch is update; this could fail
        // since mailchimp does not guarantee that submitted batches are run in
        // sequence. This could be avoided by a look-ahead at the end a batch
        // whose last record was not a tags update.
        if ($dao->mailchimp_email) {
          $member_hash = $api->getMailchimpMemberIdFromEmail($dao->mailchimp_email);
        }
        else {
          $member_hash = $api->getMailchimpMemberIdFromEmail($data['email_address']);
        }
        $requests[$id] += [
          'method' => 'POST',
          'path'   => "$url_stub/$member_hash/tags",
          'body'   => json_encode(['tags' => $data['tags']]),
        ];
      }

    }
    if ($requests) {
      // @todo consider small batches to be processed directly.
      $mailchimp_batch_id = $api->submitBatch($requests);

      // Create a batch record.
      $batch = new CRM_Mailchimpsync_BAO_MailchimpsyncBatch();
      $batch->mailchimp_batch_id = $mailchimp_batch_id;
      $batch->mailchimp_list_id = $this->mailchimp_list_id;
      // $batch->submitted_at = date('Y-m-d H:i:s');
      $batch->total_operations = count($requests);
      $batch->save();

      // Update the updates table to 'claim' these records under this batch.
      $batch_id = (int) $batch->id;
      $sql = "UPDATE civicrm_mailchimpsync_update SET mailchimpsync_batch_id = $batch_id
              WHERE id IN (" . implode(',', array_keys($requests)) .");";
      CRM_Core_DAO::executeQuery($sql);
    }

    return count($requests);
  }

  /**
   * Sync a single contact. Used by webhook.
   *
   * @param String $email - this MUST exist in the audience at Mailchimp.
   *
   * @return NULL|CRM_Mailchimpsync_BAO_MailchimpsyncCache
   */
  public function syncSingle($email) {

    // Fetch member from Mailchimp and merge it into our cache record.
    $api = $this->getMailchimpApi();
    $member_id = $api->getMailchimpMemberIdFromEmail($email);
    $member = $api->get("lists/$this->mailchimp_list_id/members/$member_id");
    $cache_entry = $this->mergeMailchimpMember($member);

    // Check any existing contact_id
    if ($cache_entry->civicrm_contact_id) {
      if (civicrm_api3('Contact', 'getcount', ['id' => $cache_entry->civicrm_contact_id]) == 0) {
        $cache_entry->civicrm_contact_id = 'null';
        $cache_entry->save();
      }
    }

    // Check if we're missing a contact ID.
    if (!$cache_entry->civicrm_contact_id) {
      $this->populateMissingContactIdsSingle($cache_entry);
      $cache_entry = $cache_entry->reloadNewObjectFromDb();
    }

    // Check if we're still missing a contact id.
    if (!$cache_entry->civicrm_contact_id) {
      $contact_id = $this->createNewContact($cache_entry);
      if (!$contact_id) {
        // we deleted this item instead, since they weren't subscribed anyway.
        return;
      }
    }

    CRM_Mailchimpsync_BAO_MailchimpsyncCache::updateCiviCRMGroups(
      ['id' => $cache_entry->id]);

    $this->dataUpdates($cache_entry->id);

    $cache_entry = $cache_entry->reloadNewObjectFromDb();
    // Finally.
    $this->reconcileQueueItem($cache_entry, TRUE);

    return $cache_entry;
  }
  // utility methods
  /**
   * Unpack the GROUP_CONCAT generated field.
   *
   * @param string Datetime mailchimp was updated.
   * @param string Subscriptions data from civicrm_groups
   *
   * @return array structure: {
   *    <group_id>: {
   *      status: 'Added',
   *      date: '2019-10-21 12:12:13',
   *      mostRecent: 'Mailchimp' | 'CiviCRM'
   *  },
   *  ...
   * }
   */
  public function parseSubs($mailchimp_updated, $subs) {
    $output = [];

    // Default status sets mostRecent to Mailchimp, since that must be true if
    // CiviCRM does not know anything about this.
    $default = ['status' => NULL, 'updated' => NULL, 'mostRecent' => 'Mailchimp'];

    $output = [(int) $this->config['subscriptionGroup'] => $default];
    foreach ($this->config['interests'] ?? [] as $group_id) {
      $output[(int) $group_id] = $default;
    }

    $mailchimp_updated = $mailchimp_updated
      ? strtotime($mailchimp_updated)
      : NULL;

    if ($subs) {
      foreach (explode('|', $subs) as $_) {
        $details = explode(';', $_);

        if (isset($output[$details[0]])) {
          // This is a group relevant to this list.
          $output[$details[0]]['status'] = $details[1];
          $output[$details[0]]['updated'] = $details[2];

          if ($mailchimp_updated) {
            if (strtotime($details[2]) >= $mailchimp_updated) {
              $output[$details[0]]['mostRecent'] = 'CiviCRM';
            }
            else {
              $output[$details[0]]['mostRecent'] = 'Mailchimp';
            }
          }
          else {
            // Mailchimp has no record of this; CiviCRM must be more up to date.
            $output[$details[0]]['mostRecent'] = 'CiviCRM';
          }
        }
      }
    }
    return $output;
  }
  /**
   * Encapsulates code around determining the 'since' date to use.
   *
   * Note we work with time() integers not dates as we need to format them in different ways.
   *
   * Returns an int time, if a sync datetime was found.
   * Returns FALSE, if a sync datetime was not found.
   *
   * @throws InvalidArgumentException if the input params specify a date we can't parse.
   *
   * @param array $params. May contain key 'since' with string datetime value.
   * @return int|FALSE
   */
  public function getRelevantSinceDate($params) {
    // Note: this is only used when the fetch begins. At that point it is
    // stored with the status of the job so that subsequent calls can continue
    // with the same setting.

    $status = $this->getStatus();
    if (($status['fetch']['offset'] ?? 0) === 0) {
      // This is the START of a new sync process, so we check for 'since' in the params.

      if (isset($params['since'])) {
        if ($params['since'] === 'ever') {
          // Forced no 'since' value.
          return FALSE;
        }
        // Use passed-in since datetime.
        $_ = strtotime($params['since']);
        if ($_) {
          return $_ - 2*60*60; // Nb. we allow 2 hours overlap to be safe.
        }
        // Invalid since date.
        throw new InvalidArgumentException("Could not parse 'since' date.");
      }
      else {
        // No since date was passed in specifically, so we see if we can find
        // the datetime of the last sync.
        if (!empty($status['lastSyncTime'])) {
          return strtotime($status['lastSyncTime']) - 2*60*60;
        }
        else {
          // No since date passed in and looks like it's never run before.
          // Need full sync.
          return FALSE;
        }
      }
    }
    else {
      // This is not the start of a run, it's a subsequent pass at a previous run.
      // We use the same 'since' time as the run started with.
      return $status['fetch']['since'] ?? FALSE;
    }
  }
  /**
   * Get the status array for this audience.
   *
   * @param bool $reset_cache
   */
  public function getStatus($reset_cache=FALSE) {
    if ($reset_cache || !$this->status_cache) {
      $status = CRM_Core_DAO::executeQuery(
        'SELECT data FROM civicrm_mailchimpsync_status WHERE list_id = %1',
        [1 => [$this->mailchimp_list_id, 'String']])->fetchValue();

      if ($status) {
        $status = unserialize($status);
      }
      if (!$status) {
        // Create initial default status.
        $status = [
          'lastSyncTime' => NULL,
          'locks' => ['fetchAndReconcile' => 'readyToFetch'],
          'log'   => [],
          'fetch' => [],
        ];

        // Save to the database so we can rely on it being there when we update it.
        CRM_Core_DAO::executeQuery(
          'INSERT INTO civicrm_mailchimpsync_status VALUES(%1, %2);',
          [
            1 => [$this->mailchimp_list_id, 'String'],
            2 => [serialize($status), 'String']
          ]);

      }
      $this->status_cache = $status;
    }

    return $this->status_cache;
  }
  /**
   * Fetch some stats for this list.
   *
   */
  public function getStats() {

    // Count of sync statuses.
    $params = [
      1 => [$this->mailchimp_list_id, 'String'],
      2 => [$this->getSubscriptionGroup(), 'Integer']
    ];

    $sql = "SELECT sync_status, COALESCE(mailchimp_status, 'missing') mailchimp_status, COALESCE(gc.status, 'missing') civicrm_status, COUNT(*) c
      FROM civicrm_mailchimpsync_cache c
      LEFT JOIN civicrm_group_contact gc ON c.civicrm_contact_id = gc.contact_id AND gc.group_id = %2
      WHERE mailchimp_list_id = %1
      GROUP BY sync_status, mailchimp_status, civicrm_status";
    $result = CRM_Core_DAO::executeQuery($sql, $params)->fetchAll();
    $stats = [
      'failed'                   => 0,
      'subscribed_at_mailchimp'  => 0,
      'subscribed_at_civicrm'    => 0,
      'to_add_to_mailchimp'      => 0,
      'cannot_subscribe'         => 0,
      'to_remove_from_mailchimp' => 0,
      'todo'                     => 0,
      'other_fails'              => 0,
      'weird'                    => [],
    ];
    foreach ($result as $row) {

      // Regardless of sync_status...
      $civicrm_is_subscribed = $row['civicrm_status'] === 'Added';
      if ($civicrm_is_subscribed) {
          $stats['subscribed_at_civicrm'] += $row['c'];
      }
      $mailchimp_is_subscribed = in_array($row['mailchimp_status'], ['subscribed', 'pending']);
      if ($mailchimp_is_subscribed) {
        $stats['subscribed_at_mailchimp'] += $row['c'];
      }


      // Now special cases
      if ($row['sync_status'] === 'fail') {
        if ($civicrm_is_subscribed && !$mailchimp_is_subscribed) {
          $stats['cannot_subscribe'] += $row['c'];
        }
        else {
          $stats['other_fails'] += $row['c'];
        }
      }
      elseif ($row['sync_status'] === 'live') {
        // This means we're waiting to update mailchimp
        if ($civicrm_is_subscribed && !$mailchimp_is_subscribed) {
          $stats['to_add_to_mailchimp'] += $row['c'];
        }
        elseif (!$civicrm_is_subscribed && $mailchimp_is_subscribed) {
          $stats['to_remove_from_mailchimp'] += $row['c'];
        }
      }
      elseif ($row['sync_status'] === 'ok') {
        // OK, we *were* in sync, are we still?

        if ($civicrm_is_subscribed && !$mailchimp_is_subscribed) {
          $stats['to_add_to_mailchimp'] += $row['c'];
        }
        elseif (!$civicrm_is_subscribed && $mailchimp_is_subscribed) {
          $stats['to_remove_from_mailchimp'] += $row['c'];
        }
      }
      elseif ($row['sync_status'] === 'todo') {
        // We're working on a reconciliation.
        $stats['todo'] += $row['c'];
      }
      else {
          $stats['weird'][] = $row;
      }
    }

    $sql = 'SELECT COUNT(*) c FROM civicrm_mailchimpsync_update up
      INNER JOIN civicrm_mailchimpsync_cache c ON c.mailchimp_list_id = %1 AND up.mailchimpsync_cache_id = c.id
      WHERE up.completed = 0';
    $stats['mailchimp_updates_pending'] = (int) CRM_Core_DAO::executeQuery($sql, $params)->fetchValue();

    $sql = 'SELECT COUNT(*) c FROM civicrm_mailchimpsync_update up INNER JOIN civicrm_mailchimpsync_cache c ON c.mailchimp_list_id = %1 AND up.mailchimpsync_cache_id = c.id
      WHERE up.completed = 0 AND up.mailchimpsync_batch_id IS NULL';
    $stats['mailchimp_updates_unsubmitted'] = (int) CRM_Core_DAO::executeQuery($sql, $params)->fetchValue();

    return $stats;
  }
  /**
   * Fetches the appropriate API object for this list.
   *
   * @return CRM_Mailchimpsync_Audience
   */
  public function getMailchimpApi() {
    return CRM_Mailchimpsync::getMailchimpApi($this->config['apiKey']);
  }
  /**
   * Finds the best email address
   *
   * 'bulk mail' wins, then primary, then any old one.
   *
   *
   * @throws InvalidArgumentException if can't find an email.
   * @param int $contact_id
   * @return string email address
   */
  public function getBestEmailForNewContact($contact_id) {
   $sql = "SELECT email
              FROM civicrm_email e
             WHERE contact_id = %1 AND e.on_hold = 0 AND email IS NOT NULL AND email != ''
          ORDER BY is_bulkmail DESC, is_primary DESC
             LIMIT 1";
    $params = [1 => [$contact_id, 'Integer']];
    $email = CRM_Core_DAO::executeQuery($sql, $params)->fetchValue();
    if (!$email) {
      throw new CRM_Mailchimpsync_CannotSyncException("Failed to find email address for contact $contact_id");
    }
    return $email;
  }
  public function log($message) {
    $log = ['time' => date('Y-m-d H:i:s'), 'message' => $message];
    // Update the config.
    $this->updateAudienceStatusSetting(function(&$config) use ($log) {
      $config['log'][] = $log;
    });
  }
  /**
   * Update setting.
   *
   * @param callable $callback
   *        This must take &$config as a parameter and alter it as needed.
   *        If no changes are needed it should return FALSE
   */
  public function updateAudienceStatusSetting(Callable $callback) {

    // Lock tables and ensure we have the latest data from db.
    //CRM_Core_DAO::executeQuery("LOCK TABLES civicrm_setting WRITE, civicrm_recurring_entity READ;");
    $config = $this->getStatus(TRUE);

    // Alter config via callback.
    $changed = $callback($config);

    if ($changed !== FALSE) {
      // Store the changed status in settings.
      CRM_Core_DAO::executeQuery(
        'UPDATE civicrm_mailchimpsync_status SET data = %1 WHERE list_id = %2',
        [
          1 => [serialize($config), 'String'],
          2 => [$this->mailchimp_list_id, 'String']
        ]);

      $this->status_cache = $config;
    }

    // Unlock tables.
    //CRM_Core_DAO::executeQuery("UNLOCK TABLES;");
    return $this;
  }
  /**
   * Attempt to obtain a lock for this audience.
   *
   * @param array $params with keys:
   * 'for' - the type of lock requested
   * 'to'  - the value to set the lock to
   * 'if'  - the lock will only be granted if the current lock status is this
   *         (or there is no such lock)
   * @return bool TRUE if lock obtained.
   */
  public function attemptToObtainLock($params) {
    $lock_obtained = FALSE;
    $purpose = $params['for'];
    $required_status = $params['if'];
    $intent = $params['to'];
    $this->updateAudienceStatusSetting(function(&$status) use ($purpose, $required_status, $intent, &$lock_obtained){

      if (!isset($status['locks'][$purpose])
        || $status['locks'][$purpose] === $required_status
      ) {
        // Ok, we can lock it.
        $status['locks'][$purpose] = $intent;
        $lock_obtained = TRUE;
      }
      else {
        // No changes to be made.
        return FALSE;
      }
    });
    return $lock_obtained;
  }
  /**
   * Update a lock.
   *
   * @param array with keys:
   * - for: the type of lock.
   * - is: the final state.
   * - andLog: log message (optional).
   * - andAlso: callback (optional).
   */
  public function updateLock(array $params) {
    $purpose = $params['for'];
    $ready = $params['to'];
    $message = $params['andLog'] ?? NULL;
    $andAlso = $params['andAlso'] ?? NULL;

    $this->updateAudienceStatusSetting(function(&$config) use ($purpose, $ready, $message, $andAlso) {
      $config['locks'][$purpose] = $ready;
      if ($message) {
        $config['log'][] = ['time' => date('Y-m-d H:i:s'), 'message' => $message];
      }
      if (is_callable($andAlso)) {
        $andAlso($config);
      }
    });
  }
  /**
   * Look up all the 2 way sync groups.
   *
   * @return array of integers (SQL safe)
   */
  public function getGroupIds() {

    $group_ids = [(int) $this->config['subscriptionGroup']];
    foreach ($this->config['interests'] ?? [] as $group_id) {
      $group_ids[] = (int) $group_id;
    }

    return $group_ids;
  }
}
