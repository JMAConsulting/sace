<?php
use CRM_Mailchimpsync_ExtensionUtil as E;

/**
 * Mailchimpsync.Fetchandreconcile API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_mailchimpsync_Fetchandreconcile_spec(&$spec) {
  $spec['force_restart'] = [
    'description' => E::ts('Developer only: Force a restart. This is not a normal thing to do; results are unpredictable.'),
    'type'        => CRM_Utils_Type::T_BOOLEAN,
    'api.default' => FALSE,
  ];
  $spec['group_id'] = [
    'description' => E::ts('The CiviCRM Group used for tracking audience/list subscriptions. If not provided, process all groups/audiences.'),
    'api.aliases' => ['id'],
  ];
  $spec['max_time'] = [
    'description' => E::ts('New jobs will not be started after this many seconds have elapsed. Set to 0 for no limit (good for CLI-driven crons). Defaults to 5 mins.'),
    'api.default' => 300,
  ];
  $spec['since'] = [
    'description' => E::ts('Fetch and reconcile contacts since this date time (or use "ever")'),
  ];
  $spec['stop_on'] = [
    'description' => E::ts('Developer only: Stop processing at this stage.'),
    'api.options' => [
      'readyToFetch',
      'readyToFixContactIds',
      'readyToCreateNewContactsFromMailchimp',
      'readyToCleanUpDuplicates',
      'readyToAddCiviOnly',
      'readyToCheckForGroupChanges',
      'readyToReconcileQueue',
      'readyToCheckForDataUpdates',
      'readyToSubmitUpdates',
    ]
  ];
  $spec['with_data'] = [
    'description' => E::ts('Do data sync'),
    'type'        => CRM_Utils_Type::T_BOOLEAN,
    'api.default' => FALSE,
  ];
}

/**
 * Mailchimpsync.Fetchandreconcile API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_mailchimpsync_Fetchandreconcile($params) {

  // Safety check that the on_hold value (if given) is valid.
  $valid = [
    'readyToFetch',
    'readyToFixContactIds',
    'readyToCreateNewContactsFromMailchimp',
    'readyToCleanUpDuplicates',
    'readyToAddCiviOnly',
    'readyToCheckForGroupChanges',
    'readyToReconcileQueue',
    'readyToCheckForDataUpdates',
    'readyToSubmitUpdates'
  ];
  if (!empty($params['stop_on']) && !in_array($params['stop_on'], $valid)) {
    throw new API_Exception('on_hold param must be one of: ' . implode(', ', $valid));
  }

  if (!empty($params['group_id'])) {
    try {
      $audiences = [CRM_Mailchimpsync_Audience::newFromGroupId($params['group_id'])->getListId()];
    }
    catch (\InvalidArgumentException $e) {
      throw new API_Exception($e->getMessage());
    }
  }
  else {
    // All audiences/lists
    $config = CRM_Mailchimpsync::getConfig();
    $audiences = array_keys($config['lists'] ?? []);
  }

  // Determine max time.
  $started_time = time();
  if (!isset($params['max_time'])) {
    // Nothing specified, keep to a safe-ish 5 minutes.
    $stop_time = $started_time + 300;
  }
  else {
    if ($params['max_time'] > 0) {
      // Use time specified.
      $stop_time = $started_time + ((int) $params['max_time']);
    }
    else {
      // No time limit. We'll use 1 day - if it's running longer than this something is surely wrong!
      // (If you want to set a longer limit, just pass in whatever you need.)
      $stop_time = $started_time + 60*60*24;
    }
  }

  // Loop audiences while we're within time limit.

  $default_params = [];
  if (!empty($params['since'])) {
    $default_params['since'] = $params['since'];
  }
  if (!empty($params['stop_on'])) {
    $default_params['stop_on'] = $params['stop_on'];
  }

  if (($params['with_data'] ?? FALSE)) {
    $default_params['with_data'] = 1;
  }

  while ($audiences && time() < $stop_time) {
    $audience = CRM_Mailchimpsync_Audience::newFromListId(array_shift($audiences));
    if (!empty($params['force_restart'])) {
      // Force reset before we start(!)
      $audience->updateAudienceStatusSetting(function(&$config) {
        $config['locks']['fetchAndReconcile'] = null;
        $config['log'] = [];
        $config['fetch'] = [];
      });
    }
    $audience->fetchAndReconcile([
      'time_limit' => $stop_time - time(), // Remaining time.
    ] + $default_params);
    $returnValues[] = [
      'list_id'  => $audience->getListId(),
      'group_id' => $audience->getSubscriptionGroup(),
      'status'   => $audience->getStatus(),
    ];
  }

  return civicrm_api3_create_success($returnValues, $params, 'Mailchimpsync', 'Fetchandreconcile');
}
