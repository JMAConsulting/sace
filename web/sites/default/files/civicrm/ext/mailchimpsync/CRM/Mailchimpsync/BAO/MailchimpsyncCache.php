<?php
use CRM_Mailchimpsync_ExtensionUtil as E;

class CRM_Mailchimpsync_BAO_MailchimpsyncCache extends CRM_Mailchimpsync_DAO_MailchimpsyncCache {

  /**
   * Create a new MailchimpsyncCache based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Mailchimpsync_DAO_MailchimpsyncCache|NULL
   *
  public static function create($params) {
    $className = 'CRM_Mailchimpsync_DAO_MailchimpsyncCache';
    $entityName = 'MailchimpsyncCache';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */


  /**
   * Abort any unsubmitted updates.
   *
   * Used when processing an unsubscribe from Mailchimp.
   */
  public function abortUpdates($message) {
    CRM_Core_DAO::executeQuery(
      'UPDATE civicrm_mailchimpsync_update
       SET error_response = %1, completed = 1
       WHERE mailchimpsync_cache_id = %2
         AND completed = 0
         AND mailchimpsync_batch_id IS NULL;',
      [
        1 => [$message, 'String'],
        2 => [$this->id, 'Integer'],
      ]);
  }

  /**
   * Returns TRUE if we consider the person to be subscribed at Mailchimp.
   *
   * @return bool
   */
  public function isSubscribedAtMailchimp() {
    return (bool) ($this->mailchimp_status && in_array($this->mailchimp_status, ['subscribed', 'pending']));
  }
  /**
   * Set CiviCRM subscription group Added.
   *
   * @param CRM_Mailchimpsync_Audience $audience
   *
   * @return CRM_Mailchimpsync_BAO_MailchimpsyncCache
   */
  public function subscribeInCiviCRM(CRM_Mailchimpsync_Audience $audience) {
    if (!$this->civicrm_contact_id) {
      throw new Exception("Cannot subscribeInCiviCRM without knowing contact_id");
    }
    $contacts = [$this->civicrm_contact_id];
    // Subscribe at CiviCRM.
    CRM_Contact_BAO_GroupContact::addContactsToGroup($contacts, $audience->getSubscriptionGroup(), 'MCsync');
    return $this;
  }
  /**
   * Set CiviCRM subscription group status Removed.
   *
   * @param CRM_Mailchimpsync_Audience $audience
   *
   * @return CRM_Mailchimpsync_BAO_MailchimpsyncCache
   *
   * @throw CRM_Mailchimpsync_CannotSyncException if we can't unsubscribe them
   * because another mailchimp cache record (i.e. a different email) has them
   * as subscribed.
   *
   * @throw BadMethodCallException if called without a CiviCRM contact ID being present.
   */
  public function unsubscribeInCiviCRM(CRM_Mailchimpsync_Audience $audience) {
    if (!$this->civicrm_contact_id) {
      throw new BadMethodCallException("Cannot unsubscribeInCiviCRM without knowing contact_id");
    }

    // Before we do this (Issue #9) we need to check if there's another
    // mailchimp record belonging to this contact that is *subscribed*
    $other_emails = new static();
    $other_emails->mailchimp_list_id = $this->mailchimp_list_id;
    $other_emails->civicrm_contact_id = $this->civicrm_contact_id;
    $other_emails->find();
    while ($other_emails->fetch()) {
      if ($other_emails->id == $this->id) {
        continue;
      }
      if ($other_emails->isSubscribedAtMailchimp()) {
        // There's another email belonging to this contact that Mailchimp has
        // down as subscribed. So we should ignore unsubscribing this old one
        // in terms of removing from the CiviCRM group.
        throw new CRM_Mailchimpsync_CannotSyncException("Refusing to unsubscribe contact because they have another email that is subscribed.");
        break;
      }
    }

    $contacts = [$this->civicrm_contact_id];
    // Record as Removed at CiviCRM.
    CRM_Contact_BAO_GroupContact::removeContactsFromGroup(
      $contacts, $audience->getSubscriptionGroup(), 'MCsync', 'Removed');
    return $this;
  }
  /**
   * Return a new object for the same record by reloading from database.
   *
   * @return CRM_Mailchimpsync_BAO_MailchimpsyncCache
   */
  public function reloadNewObjectFromDb() {
    $id = $this->id;
    $obj = new static();
    $obj->id = $id;
    $obj->find(TRUE);
    return $obj;
  }
  /**
   * Checks whether a contact's email is on hold.
   * Nb. a missing email (e.g. not yet subscribed at mailchimp) is treated as not on hold.
   *
   * @return boolean TRUE if it is on hold.
   */
  public function mailchimpEmailIsOnHoldInCivi() {
    if (!$this->mailchimp_email) {
      return FALSE;
    }
    $email = new CRM_Core_BAO_Email();
    $email->email = $this->mailchimp_email;
    $email->on_hold = 1;
    return (bool) $email->find();
  }
  /**
   * Used by API Mailchimpsync.includecontact
   *
   * @param array $contact_ids (MUST be SQL-safe)
   * @param string $status 'todo' typically.
   *
   * @return int Number of affected cache rows.
   */
  public static function setStatusByContactIDs($contact_ids, $status) {
    if (!$contact_ids) {
      throw new \InvalidArgumentException("No contact_ids passed in");
    }
    $contact_ids = implode(',', $contact_ids);
    $dao = CRM_Core_DAO::executeQuery("UPDATE civicrm_mailchimpsync_cache SET sync_status = %1 WHERE civicrm_contact_id IN ($contact_ids)",
      [
        1 => [$status, 'String']
      ]);
    return $dao->affectedRows();
  }
  /**
   * MailchimpsyncCache.get
   *
   * @param array $params
   * @return array
   */
  public static function apiSearch(array $params) {
    $i=1;

    if (empty($params['mailchimp_list_id'])) {
      throw new \Exception("must provide mailchimp_list_id");
    }

    // Look up group ID for this list id.
    $config = CRM_Mailchimpsync::getConfig();
    $group_id = (int) $config['lists'][$params['mailchimp_list_id']]['subscriptionGroup'];
    if (!$group_id) {
      throw new \Exception("Invalid list id");
    }

    $sql_params = [1 => [$group_id, 'Integer']];
    $i++;

    $is_count = ($params['options']['is_count'] ?? FALSE);

    if ($is_count) {
      $sql = 'SELECT count(mc.id) FROM civicrm_mailchimpsync_cache mc';
    }
    else {
      $sql = 'SELECT mc.*, h1.status FROM civicrm_mailchimpsync_cache mc';
    }
    $sql .= " LEFT JOIN civicrm_subscription_history h1 ON h1.contact_id = mc.civicrm_contact_id ";

    $wheres = [];
    $wheres[] = "group_id = %1";
    $wheres[] =
        "NOT EXISTS (
          SELECT id FROM civicrm_subscription_history h2
          WHERE h2.group_id = h1.group_id
          AND h2.contact_id = h1.contact_id
          AND h2.id > h1.id) ";

    if (!empty($params['civicrm_status'])) {
      $wheres[] = "h1.status = %$i";
      $sql_params[$i] = [$params['civicrm_status'], 'String'];
      $i++;
    }

    foreach (['sync_status', 'mailchimp_status', 'mailchimp_email', 'mailchimp_list_id', 'civicrm_contact_id'] as $field) {
      if (isset($params[$field])) {
        if (is_string($params[$field])) {
          $wheres[] = "$field = %$i";
          $sql_params[$i] = [$params[$field], 'String'];
          $i++;
        }
        elseif (is_array($params[$field])) {
          switch (array_keys($params[$field])[0] ?? NULL) {
          case 'LIKE':
            $wheres[] = "$field LIKE %$i";
            $sql_params[$i] = ['%' . $params[$field]['LIKE'] . '%', 'String'];
            $i++;
            break;

          default:
            throw new Exception("unsupported query");
          }
        }
        else {
          throw new Exception("unsupported query");
        }
      }
    }


    // @todo civicrm_status

    if ($wheres) {
      $sql .= " WHERE (" . implode(') AND (', $wheres) . ")";
    }

    $sql .= " ORDER BY mc.id";

    if ($is_count) {
      $results = ['count' => CRM_Core_DAO::singleValueQuery($sql, $sql_params)];
    }
    else {
      $limit = (int) ($params['options']['limit'] ?? 25);
      $offset = (int) ($params['options']['offset'] ?? 0);
      if ($limit) {
        if ($offset) {
          $sql .= " LIMIT $offset, $limit";
        }
        else {
          $sql .= " LIMIT $limit";
        }
      }
      $dao = CRM_Core_DAO::executeQuery($sql, $sql_params);
      $results = $dao->fetchAll();
      $results = ['values' => $results, 'count' => count($results)];
    }

    return $results;
  }
  /**
   * Update the 'civicrm_groups' field in our cache table.
   *
   * @param array $params. Optional keys describe filters for records to update:
   * - id: only update this cache item.
   * - list_id: only update cache items for this list.
   */
  public static function updateCiviCRMGroups($params = []) {

    // By default we will loook up the group status of any group that we have
    // any sort of sync connection with, but when called for a particular audience
    // we limit this (see below).
    $group_ids = CRM_Mailchimpsync::getAllGroupIds();

    $wheres = [];
    $sql_params = [];
    $i = 1;

    // Handle case where we're just updting a single contact.
    $v = (int) ($params['id'] ?? 0);
    if ($v > 0) {
      $wheres[] = "c.id = $v";
    }

    // Handle (normal) case where we're updating a specific list.
    if (isset($params['list_id'])) {
      // List ID given.
      $audience = CRM_Mailchimpsync_Audience::newFromListId($params['list_id']);
      $group_ids = $audience->getGroupIds();
      $wheres[] = "c.mailchimp_list_id = %$i";
      $sql_params[$i] = [$params['list_id'], 'String'];
      $i++;
    }

    $wheres = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

    if ($group_ids) {
      $group_ids_clause = "group_id IN (" . implode(',', $group_ids) . ')';
    }
    else {
      // In the case that there's no groups (e.g. just set up), this field should be empty.
      $group_ids_clause = '0';
    }

    // Increase the max length for group concat.
    // Nb. the following line is supposed to be the same, it's unclear to me when you would choose one or the other.
    // CRM_Core_DAO::executeQuery("SET @@SESSION.group_concat_max_len = 1000000;");
    CRM_Core_DAO::executeQuery("SET SESSION group_concat_max_len = 1000000;");

    $sql = "
        UPDATE civicrm_mailchimpsync_cache c
        LEFT JOIN (
          SELECT contact_id, GROUP_CONCAT(CONCAT_WS(';', group_id, status, date) SEPARATOR '|') subs
          FROM civicrm_subscription_history h1
          WHERE
            $group_ids_clause
            AND contact_id IS NOT NULL
            AND NOT EXISTS (
              SELECT id FROM civicrm_subscription_history h2
              WHERE h2.group_id = h1.group_id
              AND h2.contact_id = h1.contact_id
              AND h2.id > h1.id)
          GROUP BY contact_id
        ) AS subs_results ON c.civicrm_contact_id = subs_results.contact_id
        SET c.civicrm_groups = subs_results.subs
        $wheres
      ";
    CRM_Core_DAO::executeQuery($sql, $sql_params);
  }

}
