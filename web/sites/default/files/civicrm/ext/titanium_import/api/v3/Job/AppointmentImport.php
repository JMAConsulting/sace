<?php

//required include files
require('wp-blog-header.php');
require_once("wp-config.php");
require_once("wp-includes/wp-db.php");
require_once 'CRM/Core/Config.php';

use CRM_TitaniumImport_ExtensionUtil as E;
use Drupal\Core\Database\Database;
use Civi\Api4\Contact;

/**
 * Job.TitaniumImport API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_job_titanium_import_spec(&$spec) {}

/**
 * Job.TitaniumImport API
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
function civicrm_api3_job_titanium_import($params) {
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
    $sql = "SELECT * FROM Client"; // @lauren check after db migration
    $result = $conn->query($sql);

    $contacts = [];
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {

        



        $contacts[] = [
            'first_name' => $row['FirstName'],
            'last_name' => $row['LastName'],
            'middle_name' => $row['MiddleName'],
            'external_identifier' => $row['Id'],

            
        ];
      }
    } else {
      echo "0 results";
    }

    

    $config = CRM_Core_Config::singleton();

    try {
    $results = \Civi\Api4\Contact::save(TRUE)
        ->addDefault('contact_type', 'Individual')
        ->addRecords($contacts)
        ->setMatch([
            'external_identifier',
        ])
        ->execute();
    echo "Contacts created successfully.";
    } catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    }

    // get all the permissions associated with each role
    $all_role_permissions = [];
    foreach($role_ids as $key => $value) {
      echo '<pre>';
      echo "Role Id: " . $value;
      echo '<pre>';
      $sql = "SELECT permission FROM role_permission WHERE rid=$value and module='civicrm'";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
        $role_permissions = [];
        while($row = $result->fetch_assoc()) {
          array_push($role_permissions, $row["permission"]);
        }
        // create an array with the key as the role name and value as an array of permissions
        $all_role_permissions[$key] = $role_permissions;
        // unset($role_permissions);
      } else {
        echo "0 results";
      }
    }

    // use CRM_Utils_String::munge( strtolower( $permission ) ); to convert the drupal role first

    // arrays used to store the new group and capability ids needed for later steps of the migration
    $all_groups = [];
    $all_capabilities = [];
    // array that maps the new wordpress group ids to the new wordpress group capabilities
    $all_groups_with_capabilities = [];
    // iterate through the roles and permissions
    foreach ($all_role_permissions as $role => $permissions) {
      // create a new group with the name set as the drupal role
      $creator_id  = get_current_user_id();
      $name = $role;
      $group_id = Groups_Group::create( compact( "creator_id", "datetime", "parent_id", "description", "name" ) );
      // add the new group id to the array
      if ( $group_id ) {
        $all_groups[$name] = $group_id;
        echo '<pre>';
        echo "Created new group: " . $name;
        echo '<pre>';
      }
      // read the group id if the group already exists and add it to the array
      else {
        $existing_group = Groups_Group::read_by_name( $name );
        $all_groups[$name] = $existing_group->group_id;
        echo '<pre>';
        echo "Group '" . $name . "' already exists. Skipping...";
        echo '<pre>';
      }
      // initalize the group with the wordpress group id and the 
      $all_groups_with_capabilities[$name] = [
        'gid' => $all_groups[$name],
        'capabilities' => [],
      ];
      // iterate through the array of permissions associated with the role
      foreach($permissions as $permission) {
        // create a new capability with the same name as the drupal capability
        $capability = CRM_Utils_String::munge( strtolower( $permission ) );
        $capability_id = Groups_Capability::create( compact( "capability", "description" ) );
         // add the new capability id to the array
        if ( $capability_id ) {
          $all_capabilities[$capability] = $capability_id;
          echo '<pre>';
          echo "Created new capability: " . $capability;
          echo '<pre>';
        }
        // read the capability id if the group already exists and add it to the array
        else {
          $existing_capability = Groups_Capability::read_by_capability( $capability );
          $all_capabilities[$capability] = $existing_capability->capability_id;
          echo '<pre>';
          echo "Capability '" . $capability . "' already exists. Skipping...";
          echo '<pre>';
        }
        // map the wordpress group capability id to the wordpress group id
        $all_groups_with_capabilities[$name]['capabilities'][$capability] = $all_capabilities[$capability];
      }
    }

    // assign the wordpress capabilties to the wordpress groups
    foreach ($all_groups_with_capabilities as $group_name => $group) {
      foreach($group['capabilities'] as $capability_name => $capability) {
        Groups_Group_Capability::create( array( 'group_id' => $group['gid'], 'capability_id' => $capability) );
        echo '<pre>';
        echo "Added Capability '" . $capability_name . "' to Group: " . $group_name;
        echo '<pre>';
      }
    }

    // create an instance of the class because the functions contain the '$this' variable
    $CiviCRM_Groups_Sync_CiviCRM = new CiviCRM_Groups_Sync_CiviCRM($plugin);
    // use the CiviCRM api to get the existing CiviCRM groups
    $groups = civicrm_api4('Group', 'get', [
      'select' => [
        'name',
      ],
    ]);
    // map the api results to an array
    $exiting_civicrm_group_names = [];
    foreach($groups as $group){
      array_push($exiting_civicrm_group_names, $group['name']);
    }
    // create a new CiviCRM group linked to the WordPress group if the CiviCRM group does not exist already
    foreach ($all_groups as $group_id) {
      $group = Groups_Group::read($group_id);
      if (!in_array($group->name, $exiting_civicrm_group_names)) {
        $CiviCRM_Groups_Sync_CiviCRM->group_create_from_wp_group( $group );
        echo '<pre>';
        echo "Created CiviCRM group: '" . $group->name . "'";
        echo '<pre>';
      }
      else {
        echo '<pre>';
        echo "CiviCRM group '" . $group->name . "' already exists. Skipping...";
        echo '<pre>';
      }
    }

    // get all active usernames and emails from the Drupal database
    $all_users = [];
    $sql = "SELECT * FROM users WHERE status = 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $user_details = [];
        $user_details['username'] = $row['name'];
        $user_details['email'] = $row['mail'];
        $all_users[$row['uid']] = $user_details;
      }
    } else {
      echo "0 results";
    }
    // create a new user with the credentials from Drupal and a dummy password that will need to be reset
    foreach($all_users as $user) {
      $userdata = [
        'user_login' => $user['username'],
        'user_pass' => NULL,
        'user_email' => $user['email'],
      ];
      wp_insert_user( $userdata );
      echo '<pre>';
      echo "Created user '" . $user['username'] . "'";
      echo '<pre>';
    }
    // map the user ids from Drupal to the new user ids from WordPress
    $all_wp_users = [];
    foreach($all_users as $uid => $user) {
      $email = $user['email'];
      $wpdb_results = $wpdb->get_results( 
        $wpdb->prepare("SELECT ID FROM {$wpdb->users} WHERE user_email='$email'")
      );
      $all_wp_users[$uid] = $wpdb_results[0]->ID;
    }
    // assign the user to the new WordPress group based on the role assigned in Drupal
    foreach($all_wp_users as $drupal_uid => $wp_uid) {
      $sql = "SELECT * FROM users_roles ur INNER JOIN role r ON r.rid=ur.rid WHERE uid=$drupal_uid";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          // add the user to the group if the user has not been added already
          if ( !Groups_User_Group::read( $wp_uid, $all_groups[$row['name']]) ) {
            Groups_User_Group::create(
              array(
                'user_id' => $wp_uid,
                'group_id' => $all_groups[$row['name']],
              )
            );
          }
        }
      } else {
        echo "0 results";
      }
    }
    $conn->close();
    exit;

    return civicrm_api3_create_success($returnValues, $params, 'Job', 'titanium_import');
  }
  catch (Exception $e) {
    throw new API_Exception('Failed to import Titanium data', ['exception' => $e->getMessage()]);
  }
}  
