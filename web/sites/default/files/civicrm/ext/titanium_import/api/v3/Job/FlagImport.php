<?php

use CRM_TitaniumImport_ExtensionUtil as E;
use Drupal\Core\Database\Database;

/**
 * Job.FlagImport API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_job_flag_import_spec(&$spec) {}

/**
 * Job.FlagImport API
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
function civicrm_api3_job_flag_import($params) {
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

    // get flag name from lookup table?? (an assumption + I'm not totally positive which field holds the name)
    $sql = "
      SELECT *
      FROM clientflag cf
      LEFT JOIN lookup l ON cf.TypeId = l.Id
    ";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) { // how to link in appointcode?
      while($row = $result->fetch_assoc()) {
        // get contact ID
        $clientID = \Civi\Api4\Contact::get(TRUE)
          ->addWhere('external_identifier', '=', $row['ClientId'])
          ->execute()
          ->first()['id'];

        $flagType = \Civi\Api4\OptionValue::get(TRUE)
          ->addJoin('Saceflags AS saceflags', 'LEFT', ['value', '=', 'saceflags.activity_type_id'])
          ->addWhere('option_group_id', '=', 2)
          ->addWhere('saceflags.used_for_flagging_contacts', '=', TRUE)
          ->addWhere('label', '=', $row['l.TableCode'])
          ->execute();

        // If flag activity type doesn't exist in Civi, create a new one

        if(!count($flagType)) {
          $flagType = \Civi\Api4\OptionValue::create(TRUE)
            ->addValue('option_group_id', 2)
            ->addValue('name', $row['l.TableCode'])
            ->addValue('label', $row['l.TableCode'])
            ->execute();

          \Civi\Api4\Saceflags::create(TRUE)
            ->addValue('activity_type_id', $flagType['value'])
            ->addValue('used_for_flagging_contacts', TRUE)
            ->execute();

        }
        // Need to import active status (TO DO, need to know mappings)
        $flag = \Civi\Api4\Activity::create(TRUE)
          ->addValue('duration', $row['Length'])
          ->addValue('created_date', date('Y-m-d', strtotime($row['AddDate'])))
          ->addValue('activity_type_id', $flagType[0]['value'])
          ->addValue('target_contact_id', [
            $clientID,
          ])
          ->addValue('details', $row['Message'])
          ->addValue('source_contact_id', 2) // Setting source to SACE admin for now
          ->execute();
      }
      $returnValues = "Successfully imported flags into CiviCRM.";
    } else {
      $returnValues = "0 results";
    }
    return civicrm_api3_create_success($returnValues, $params, 'Job', 'flag_import');
  }
  catch (Exception $e) {
    throw new API_Exception('Failed to import Titanium data', ['exception' => $e->getMessage()]);
  }
}  
