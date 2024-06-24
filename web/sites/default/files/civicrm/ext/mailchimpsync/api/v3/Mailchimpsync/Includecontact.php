<?php
use CRM_Mailchimpsync_ExtensionUtil as E;

/**
 * Mailchimpsync.Includecontact API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_mailchimpsync_Includecontact_spec(&$spec) {
  $spec['contact_id']['api.required'] = 1;
}

/**
 * Mailchimpsync.Includecontact API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_mailchimpsync_Includecontact($params) {

  $contact_ids = [];
  if (is_array($params['contact_id'])) {
    foreach ($params['contact_id'] as $given) {
      $_ = (int) $given;
      if (!($_ > 0)) {
        throw new API_Exception('At least one contact ID was not positive integer.');
      }
      $contact_ids[$_] = 1;
    }
  }
  else {
    $_ = (int) $params['contact_id'];
    if (!($_ > 0)) {
      throw new API_Exception('At contact ID was not positive integer.');
    }
    $contact_ids[$_] = 1;
  }

  $affected = CRM_Mailchimpsync_BAO_MailchimpsyncCache::setStatusByContactIDs(array_keys($contact_ids), 'todo');

  return [
    'is_error' => 0,
    'version'  => 3,
    'values'   => [
      'inputCount' => count($contact_ids),
      'affected'   => $affected,
    ]
  ];
  // return civicrm_api3_create_success(['success' => 1], $params, 'Mailchimpsync', 'Includecontact');
}
