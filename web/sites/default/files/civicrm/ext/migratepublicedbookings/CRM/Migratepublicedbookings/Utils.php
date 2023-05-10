<?php

use CRM_Migratepublicedbookings_ExtensionUtil as E;

require_once 'migratepublicedbookings.variables.php';

class CRM_Migratepublicedbookings_Utils {

  /**
   * static $results = [];
   */
  public static function importBookings($table, $year) {
    $constants = get_defined_constants(FALSE);
    $bookings = CRM_Core_DAO::executeQuery("SELECT * FROM $table")->fetchAll();

    foreach ($bookings as $key => $booking) {
        CRM_Core_Error::Debug('key', $key);
        CRM_Core_Error::Debug('legacy', $legacy_id);
      //Testing
      if ($legacy_id >= 5) {
        exit;
      }
      //End Testing

      $legacy_id = $key + 1;
      $record = [];
      // Create Record object with all fields to migrate
      self::createRecord($booking, $record, $constants);

      // Find staff assigned contact id
      self::findStaffAssigned($booking, $record, $constants);

      // Presentation Method
      self::setPresentationMethod($year, $record);

      // Organization
      self::setOrganization($booking, $record);

      // Audience
      self::setAudienceType($booking, $record);

      // Q1 - Q8
      self::setQ1toQ8($booking, $record, $constants);

      // Create Activities
      self::createActivities($activities, $constants, $record, $table, $legacy_id);
    }
    // return $results;
  }

  private static function createRecord($booking, &$record, &$constants) {
    // Activity Date
    $record['date'] = date_format(date_create("2019-$booking[Month]-$booking[Date]"), 'Y-m-d');
    // Presentation Topic
    $record['presentation_topic'] = $booking['Presentation_Topic'];

    // Youth or Adult
    $record['youth_or_adult'] = $booking['Youth_or_Adult'];

    // Activity type based on youth or adult
    if ($booking['Youth_or_Adult'] == 'Youth') {
      $record['activity_type_id'] = $constants['Youth_Activity'];
    }
    elseif ($booking['Youth_or_Adult'] == 'Adult') {
      $record['activity_type_id'] = $constants['Adult_Activity'];
    }

    $record['postal_code'] = $booking['Postal_Code'];
    $record['activity_duration'] = $booking['Length'];
    $record['number_of_participants'] = $booking['Number_of_Participants'];
    $record['number_of_returned_evaluations'] = $booking['Number_of_Returned_Evaluations'];
    $record['facilitating_program'] = $booking['Facilitating_Program'];
  }

  private static function findStaffAssigned($booking, &$record, &$constants) {
    // Get staff's email ID from constants
    $staff = $constants['Staff'];
    for ($i = 1; !empty($booking["Presenter_$i"]); ++$i) {
      if (!empty($booking["Presenter_$i"])) {
        // Find Staff Assigned i
        $email = $staff[current(preg_grep('/^' . $booking["Presenter_$i"] . '/i', array_keys($staff)))];
        $contacts = \Civi\Api4\Contact::get()
          ->addSelect('id')
          ->addJoin('Email AS email', 'LEFT', ['id', '=', 'email.contact_id'])
          ->addWhere('email.email', '=', $email)
          ->execute();
        $record["staff_assigned"][] = $contacts->first()['id'];
      }
    }
  }

  private static function setPresentationMethod($year, &$record) {
    // if year between 2014 - 2019, In Person
    if ($year >= 2014 && $year <= 2019) {
      $record['presentation_method'] = "In-Person Presentation";
    }
    else {
      $record['presentation_method'] = $booking['Presentation_Method'];
    }
  }

  private static function setOrganization($booking, &$record) {
    // Check if Organization contact exists
    $organization = \Civi\Api4\Contact::get()
      ->addWhere('contact_type', '=', 'Organization')
      ->addWhere('organization_name', '=', $booking['Organization'])
      ->setLimit(1)
      ->execute();
    if (!empty($organization[0])) {
      $record['organization'] = $organization[0]['id'];
    }
    // Create Organization Contact
    else {
      $org = \Civi\Api4\Contact::create()
        ->addValue('contact_type', 'Organization')
        ->addValue('display_name', $booking['Organization'])
        ->addValue('organization_name', $booking['Organization'])
        ->execute();
      $record['organization'] = $org[0]['id'];

      // Add postal code for the created Organization
      \Civi\Api4\Address::create()
        ->addValue('postal_code', $record['postal_code'])
        ->addValue('contact_id', $record['organization'])
        ->execute();
    }
  }

  private static function setAudienceType($booking, &$record) {
    if (array_key_exists('Audience_Type', $booking)) {
      $record['audience_type'][] = $booking['Audience_Type'];
    }
    for ($i = 2; !empty($booking["Audience_Type$i"]); ++$i) {
      if (!empty($booking["Audience_Type$i"])) {
        // Audience Type i
        $record['audience_type'][] = $booking["Audience_Type$i"];
      }
    }
  }

  private static function setQ1toQ8($booking, &$record, &$constants) {
    // Q1
    $record[$constants['Q1']] = [
      "strongly_agree" => $booking['STRONGLY_AGREE1'],
      "agree" => $booking['AGREE1'],
      "dont_know" => $booking['DONT_KNOW1'],
      "disagree" => $booking['DISAGREE1'],
      "strongly_disagree" => $booking['STRONGLY_DISAGREE1'],
    ];

    // Q2
    $record[$constants['Q2']] = $booking["Number_Surveyed_Able_to_List_Something_Learned"];

    // Q3
    $record[$constants['Q3']] = [
      "strongly_agree" => $booking['STRONGLY_AGREE3'],
      "agree" => $booking['AGREE3'],
      "dont_know" => $booking['DONT_KNOW3'],
      "disagree" => $booking['DISAGREE3'],
      "strongly_disagree" => $booking['STRONGLY_DISAGREE3'],
    ];

    // Q4
    $record[$constants['Q4']] = $booking["Number_Surveyed_Able_to_List_Something_To_Do_to_Support"];

    // Q5
    $record[$constants['Q5']] = [
      "strongly_agree" => $booking['STRONGLY_AGREE5'],
      "agree" => $booking['AGREE5'],
      "dont_know" => $booking['DONT_KNOW5'],
      "disagree" => $booking['DISAGREE5'],
      "strongly_disagree" => $booking['STRONGLY_DISAGREE5'],
    ];

    if ($year >= 2018) {
      // Q6
      $record[$constants['Q6']] = $booking["Number_Surveyed_Able_to_List_a_Resource"];

      // Q7
      $record[$constants['Q7']] = [
        "strongly_agree" => $booking['STRONGLY_AGREE7'],
        "agree" => $booking['AGREE7'],
        "dont_know" => $booking['DONT_KNOW7'],
        "disagree" => $booking['DISAGREE7'],
        "strongly_disagree" => $booking['STRONGLY_DISAGREE7'],
      ];

      // Q8
      $record[$constants['Q8']] = [
        "strongly_agree" => $booking['STRONGLY_AGREE8'],
        "agree" => $booking['AGREE8'],
        "dont_know" => $booking['DONT_KNOW8'],
        "disagree" => $booking['DISAGREE8'],
        "strongly_disagree" => $booking['STRONGLY_DISAGREE8'],
      ];
    }
  }

  private static function createActivities(&$activities, &$constants, &$record, $table, $legacy_id) {
    $options = array_keys($record[$constants['Q1']]);
    $activities = [];
    if ($year >= 2018) {
      $likert_questions = ['Q1', 'Q3', 'Q5', 'Q7', 'Q8'];
    }
    else {
      $likert_questions = ['Q1', 'Q3', 'Q5'];
    }
    foreach ($options as $o) {
      foreach ($likert_questions as $q) {
        for ($j = 0; $j < $record[$constants[$q]][$o]; $j++) {
          for ($k = 1; $k <= $record['number_of_returned_evaluations']; $k++) {
            if (empty($activities[$k][$constants[$q]])) {
              $activities[$k][$constants[$q]] = $o;
              break;
            }
          }
        }
      }
    }
    if ($year >= 2018) {
      $feedback_questions = ['Q2', 'Q4', 'Q6'];
    }
    else {
      $feedback_questions = ['Q2', 'Q4'];
    }
    foreach ($feedback_questions as $q) {
      for ($j = $record[$constants[$q]]; $j > 0; $j--) {
        for ($k = 1; $k <= $record['number_of_returned_evaluations']; $k++) {
          if (empty($activities[$k][$constants[$q]])) {
            $activities[$k][$constants[$q]] = "Migrated Booking, able to answer";
            break;
          }
        }
      }
    }
    $results = [];
    // Create Activities
    foreach ($activities as $activity) {
      $created = \Civi\Api4\Activity::create()
        ->addValue($constants['Target_Contact_Id'], $record['organization'])
        ->addValue($constants['Youth_or_Adult'], $record['youth_or_adult'])
        ->addValue($constants['Presentation_Method'], $record['presentation_method'])
        ->addValue($constants['Presentation_Topics'], $record['presentation_topic'])
        ->addValue($constants['Activity_Date'], $record['date'])
        ->addValue($constants['Activity_Duration'], $record['activity_duration'])
        ->addValue($constants['Activity_Type_Id'], $record['activity_type_id'])
        ->addValue($constants['Number_Participants'], $record['number_of_participants'])
        ->addValue($constants['Staff_Assigned'], $record['staff_assigned'])
        ->addValue($constants['Facilitating_Program'], $record['facilitating_program'])
        ->addValue($constants['Audience'], $record['audience_type'])
        ->addValue($constants['Q1_Field'], $activity[$constants['Q1']])
        ->addValue($constants['Q2_Field'], $activity[$constants['Q2']])
        ->addValue($constants['Q3_Field'], $activity[$constants['Q3']])
        ->addValue($constants['Q4_Field'], $activity[$constants['Q4']])
        ->addValue($constants['Q5_Field'], $activity[$constants['Q5']])
        ->addValue($constants['Q6_Field'], $activity[$constants['Q6']])
        ->addValue($constants['Q7_Field'], $activity[$constants['Q7']])
        ->addValue($constants['Q8_Field'], $activity[$constants['Q8']])
        ->addValue($constants['Source_Contact_Id'], $constants['SACE_Contact'])
        ->execute();

      //Add log
      $result = \Civi\Api4\BookingImportLog::create()
        ->addValue('activity_id', $created[0]["id"])
        ->addValue('table_name', $table)
        ->addValue('legacy_id', $legacy_id)
        ->execute();
    }
    $results[] = $result;
    if ($legacy_id >= 3) {
      return $results;
    }
  }

}
