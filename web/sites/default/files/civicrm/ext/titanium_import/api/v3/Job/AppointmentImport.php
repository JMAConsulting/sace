<?php

use CRM_TitaniumImport_ExtensionUtil as E;
use Drupal\Core\Database\Database;

/**
 * Job.AppointmentImport API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_job_appointment_import_spec(&$spec) {}

/**
 * Job.AppointmentImport API
 * Implements a scheduled job to import Titanium data.
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
function civicrm_api3_job_appointment_import($params) {
  try {
    // Database connection credentials
    $servername = "localhost";
    $dbname = "";
    $username = "";
    $password = "";

    // Connect to database with Titanium data using credientials
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    // for appointresource, is this the counsellor?
    $sql = "
      SELECT *
      FROM appoint a
      LEFT JOIN appointclient ac ON a.Id = ac.AppointId
      LEFT JOIN appointresource ar ON a.Id = ar.AppointId
      LEFT JOIN appointcode acode ON a.AppointCodeId = acode.Id
      LEFT JOIN casenote cn ON a.Id = cn.AppointId
    ";
    $result = $conn->query($sql);

    $appointments = [];
    if ($result->num_rows > 0) { // how to link in appointcode?
      while($row = $result->fetch_assoc()) {
        // get contact ID and Resource ID
        $clientID = \Civi\Api4\Contact::get(TRUE)
          ->addWhere('external_identifier', '=', $row['ac.ClientId'])
          ->execute()
          ->first()['id'];
        $resourceID = \Civi\Api4\Contact::get(TRUE)
          ->addWhere('external_identifier', '=', $row['ac.ResourceId'])
          ->execute()
          ->first()['id'];
        
        $appointment = \Civi\Api4\Activity::create(TRUE)
          ->addValue('activity_date_time', date('Y-m-d', strtotime($row['Start'])))
          ->addValue('duration', $row['Length'])
          ->addValue('created_date', date('Y-m-d', strtotime($row['AddDate'])))
          ->addValue('activity_type_id:label', $row['acode.Description'])
          ->addValue('target_contact_id', [
            $clientID,
          ])
          ->addValue('location', $row['Location'])
          ->addValue('subject', $row['Description'])
          ->addValue('details', $row['Comment'])
          ->addValue('source_contact_id', $resourceID)
          ->addValue('assignee_contact_id', [
            $resourceID,
          ])
          ->execute();

          // If there is a casenote attached... how to deal with multiple notes
          // attached to one appointment?
          if($row['cn.Id']) {
            // where is note content stored?
            $note = \Civi\Api4\Note::create(TRUE)
              ->addValue('note', $row['cn.NarrativeText'])
              ->addValue('entity_table', 'civicrm_activity')
              ->addValue('entity_id', $appointment[0]['id'])
              ->addValue('created_date', date('Y-m-d', strtotime($row['cn.AddDate'])))
              ->addValue('note_date', date('Y-m-d', strtotime($row['cn.Date'])))
              ->execute();

            $results = \Civi\Api4\LockedNote::create(TRUE)
              ->addValue('note_id', $note[0]['id'])
              ->addValue('is_locked', (bool)$row['cn.LockStatusModifier'])
              ->execute();
          }
      }
      $returnValues = "Successfully imported appointments into CiviCRM.";
    } else {
      $returnValues = "0 results";
    }
    return civicrm_api3_create_success($returnValues, $params, 'Job', 'appointment_import');
  }
  catch (Exception $e) {
    throw new API_Exception('Failed to import Titanium data', ['exception' => $e->getMessage()]);
  }
}  
