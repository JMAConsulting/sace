<?php

//required include files
require_once 'CRM/Core/Config.php';

use CRM_TitaniumImport_ExtensionUtil as E;
use Drupal\Core\Database\Database;
use Civi\Api4\Contact;

/**
 * Job.ClientImport API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_job_client_import_spec(&$spec) {}

/**
 * Job.ClientImport API
 * Implements a scheduled job to import Titanium client data.
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
function civicrm_api3_job_client_import($params) {
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

    // get all the roles created in Drupal
    $sql = "SELECT * FROM client";
    $result = $conn->query($sql);

    $contacts = [];
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {

        $results = \Civi\Api4\Contact::create(TRUE)
          ->addValue('contact_type', 'Individual')
          ->addValue('contact_sub_type', [
            'Client',
          ])
          ->addValue('first_name', $row['FirstName'])
          ->addValue('last_name', $row['LastName'])
          ->addValue('middle_name', $row['MiddleName'])
          ->addValue('created_date', date('Y-m-d', strtotime($row['AddDate'])))
          ->addValue('external_identifier', $row['Id'])
          ->addValue('birth_date', date('Y-m-d', strtotime($row['DateOfBirth'])))
          ->addValue('nick_name', $row['PreferredName'])
        // 'gender_id' => $row['Sex'], needs option value conversion and need sample data to test possible row values
          ->addChain('create_email', \Civi\Api4\Email::create(TRUE)
            ->addValue('contact_id', '$id')
            ->addValue('location_type_id', 3)
            ->addValue('email', $row['DateOfBirth'])
          )
          ->addChain('create_phone1', \Civi\Api4\Phone::create(TRUE) // Ok to contact rules?
            ->addValue('contact_id', '$id')
            ->addValue('location_type_id', 3)
            ->addValue('phone', $row['Phone1'])
          )
          ->addChain('create_phone2', \Civi\Api4\Phone::create(TRUE)
            ->addValue('contact_id', '$id')
            ->addValue('location_type_id', 4)
            ->addValue('phone', $row['Phone2'])
          )
          ->addChain('create_phone3', \Civi\Api4\Phone::create(TRUE) // How to deal with OK to contact fields?
            ->addValue('contact_id', '$id')
            ->addValue('location_type_id', 4)
            ->addValue('phone', $row['Phone3'])
          )
          ->addChain('create_address1', \Civi\Api4\Address::create(TRUE)
            ->addValue('contact_id', '$id')
            ->addValue('location_type_id', 1)
            ->addValue('street_address', $row['Address1'])
            ->addValue('postal_code', $row['ZipCode1'])
            ->addValue('OK_to_Contact.OK_to_contact_at_Address_', ($row['OkToContactAddress1Rule'] == 2) ? 1 : 0) // 2 == true
          )
          ->addChain('create_address2', \Civi\Api4\Address::create(TRUE)
            ->addValue('contact_id', '$id')
            ->addValue('location_type_id', 4)
            ->addValue('street_address', $row['Address2'])
            ->addValue('postal_code', $row['ZipCode2'])
            ->addValue('OK_to_Contact.OK_to_contact_at_Address_', ($row['OkToContactAddress2Rule'] == 2) ? 1 : 0) // 2 == true
          )
          ->addChain('create_comment', \Civi\Api4\Note::create(TRUE)
            ->addValue('entity_id', '$id')
            ->addValue('entity_table', 'civicrm_contact')
            ->addValue('note', $row['Comment'])
          )          
          ->execute();
      }
      $returnValues = "Successfully imported clients into CiviCRM.";
    } else {
      $returnValues = "0 results";
    }

    return civicrm_api3_create_success($returnValues, $params, 'Job', 'client_import');
  }
  catch (Exception $e) {
    throw new API_Exception('Failed to import Titanium client data', ['exception' => $e->getMessage()]);
  }
}  
