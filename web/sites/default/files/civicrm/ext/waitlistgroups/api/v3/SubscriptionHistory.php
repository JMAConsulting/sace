<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 * This api exposes CiviCRM Subscription History records.
 *
 * @package CiviCRM_APIv3
 */

/**
 * Add a Subscription History.
 *
 * @param array $params
 *
 * @return array
 *   API result array
 */
function civicrm_api3_subscription_history_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'SubscriptionHistory');
}

/**
 * Deletes an existing Subscription History.
 *
 * @param array $params
 *
 * @return array
 *   API result array
 * @throws \CRM_Core_Exception
 */
function civicrm_api3_subscription_history_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Retrieve one or more Subscription Histories.
 *
 * @param array $params
 *
 * @return array
 *   API result array
 */
function civicrm_api3_subscription_history_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, TRUE, 'SubscriptionHistory');
}


