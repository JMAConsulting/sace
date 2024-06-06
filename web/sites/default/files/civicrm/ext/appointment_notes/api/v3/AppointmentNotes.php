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
 * This api exposes CiviCRM appointment_notes records.
 *
 * @package CiviCRM_APIv3
 */

/**
 * Add an AppointmentNote for a contact.
 *
 * @param array $params
 *
 * @return array
 *   API result array
 */
function civicrm_api3_appointment_notes_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'AppointmentNotes');
}

/**
 * Adjust Metadata for Create action.
 *
 * The metadata is used for setting defaults, documentation & validation.
 *
 * @param array $params
 *   Array of parameters determined by getfields.
 */
//function _civicrm_api3_appointment_notes_create_spec(&$params) {
//  $params['contact_id']['api.required'] = 1;
//}

/**
 * Deletes an existing AppointmentNotes.
 *
 * @param array $params
 *
 * @return array
 *   API result array
 * @throws \CRM_Core_Exception
 */
function civicrm_api3_appointment_notes_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Retrieve one or more appointment_notes.
 *
 * @param array $params
 *
 * @return array
 *   API result array
 */
function civicrm_api3_appointment_notes_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, TRUE, 'AppointmentNotes');
}
