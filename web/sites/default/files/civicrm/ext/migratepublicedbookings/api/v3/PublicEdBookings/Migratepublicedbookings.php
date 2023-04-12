<?php
use CRM_Migratepublicedbookings_ExtensionUtil as E;

/**
 * PublicEdBookings.Migratepublicedbookings API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_public_ed_bookings_Migratepublicedbookings_spec(&$spec) {
  $spec['table_name']['api.required'] = 1;
}

/**
 * PublicEdBookings.Migratepublicedbookings API
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
function civicrm_api3_public_ed_bookings_Migratepublicedbookings($params) {
  if (!empty($params['table_name'])) {
    $returnValues = CRM_Migratepublicedbookings_Utils::importBookings($params['table_name']);
    return civicrm_api3_create_success($returnValues, $params, 'PublicEdBookings', 'migratepublicedbookings');
  }
  else {
    throw new API_Exception(/*error_message*/ 'Please specify a table containing an exported list of PublicEdBookings', /*error_code*/ 'table_name_required');
  }  
}
