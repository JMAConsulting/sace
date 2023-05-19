<?php

use CRM_Migratepublicedbookings_ExtensionUtil as E;

require_once 'migratepublicedbookings.variables.php';

class CRM_Migratepublicedbookings_Utils {

  public static function importBookings($table, $year) {
    $constants = get_defined_constants(FALSE);
    $bookings = CRM_Core_DAO::executeQuery("SELECT * FROM $table limit 1")->fetchAll();    

    $results = [];
    $record = [];
    foreach ($bookings as $booking) {
      // Create Record object with all fields to migrate
      self::createRecord($year, $booking, $record, $constants);

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

      // Create Booking Activity
      $booking_activity = self::createBookingActivity($constants, $record);

      // Create Eval Activity
      self::createEvalActivity($booking_activity->first()['id'], $constants, $record);
      
      // Create Feedback Activities
      $results[] = self::createFeedbackActivities($booking_activity->first()['id'], $constants, $record);

    }
    return $results;
  }

  private static function createRecord($year, $booking, &$record, &$constants) {
    // Activity Date
    $record['date'] = date_format(date_create("$year-$booking[Month]-$booking[Date]"), 'Y-m-d');
    

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
    $record['legacy_id'] = $booking['id'];
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
      "Strongly Agree" => $booking['STRONGLY_AGREE1'],
      "Agree" => $booking['AGREE1'],
      "Somewhat Agree" => $booking['DONT_KNOW1'],
      "Disagree" => $booking['DISAGREE1'],
      "Strongly Disagree" => $booking['STRONGLY_DISAGREE1'],
    ];

    // Q2
    $record[$constants['Q2']] = $booking["Number_Surveyed_Able_to_List_Something_Learned"];

    // Q3
    $record[$constants['Q3']] = [
      "Strongly Agree" => $booking['STRONGLY_AGREE3'],
      "Agree" => $booking['AGREE3'],
      "Somewhat Agree" => $booking['DONT_KNOW3'],
      "Disagree" => $booking['DISAGREE3'],
      "Strongly Disagree" => $booking['STRONGLY_DISAGREE3'],
    ];

    // Q4
    $record[$constants['Q4']] = $booking["Number_Surveyed_Able_to_List_Something_To_Do_to_Support"];

    // Q5
    $record[$constants['Q5']] = [
      "Strongly Agree" => $booking['STRONGLY_AGREE5'],
      "Agree" => $booking['AGREE5'],
      "Somewhat Agree" => $booking['DONT_KNOW5'],
      "Disagree" => $booking['DISAGREE5'],
      "Strongly Disagree" => $booking['STRONGLY_DISAGREE5'],
    ];

    if ($year >= 2018) {
      // Q6
      $record[$constants['Q6']] = $booking["Number_Surveyed_Able_to_List_a_Resource"];

      // Q7
      $record[$constants['Q7']] = [
        "Strongly Agree" => $booking['STRONGLY_AGREE7'],
        "Agree" => $booking['AGREE7'],
        "Somewhat Agree" => $booking['DONT_KNOW7'],
        "Disagree" => $booking['DISAGREE7'],
        "Strongly Disagree" => $booking['STRONGLY_DISAGREE7'],
      ];

      // Q8
      $record[$constants['Q8']] = [
        "Strongly Agree" => $booking['STRONGLY_AGREE8'],
        "Agree" => $booking['AGREE8'],
        "Somewhat Agree" => $booking['DONT_KNOW8'],
        "Disagree" => $booking['DISAGREE8'],
        "Strongly Disagree" => $booking['STRONGLY_DISAGREE8'],
      ];
    }
  }

  private static function createBookingActivity(&$constants, &$record) {    
    $booking = \Civi\Api4\Activity::create()
      ->addValue($constants['Youth_or_Adult'], $record['youth_or_adult'])
      ->addValue($constants['Presentation_Method'], $record['presentation_method'])
      ->addValue($constants['Presentation_Topics'], $record['presentation_topic'])
      ->addValue($constants['Activity_Date_Time'], $record['date'])
      ->addValue($constants['Activity_Duration'], $record['activity_duration'])
      ->addValue($constants['Activity_Type_Id'], $record['activity_type_id'])
      ->addValue($constants['Number_Participants'], $record['number_of_participants'])
      ->addValue($constants['Staff_Assigned'], $record['staff_assigned'])
      ->addValue($constants['Facilitating_Program'], $record['facilitating_program'])
      ->addValue($constants['Audience'], $record['audience_type'])
      ->addValue($constants['Target_Contact_Id'], $record['organization'])
      ->addValue($constants['Source_Contact_Id'], $constants['SACE_Contact'])
      ->execute();
      return $booking;
  }

  private static function createFeedbackActivities($booking, &$constants, &$record) {
    $options = array_keys($record[$constants['Q1']]);
    $activities = [];
    if ($year >= 2018) {
      $likert_questions = ['Q1', 'Q3', 'Q6', 'Q7', 'Q8'];
    }
    else {
      $likert_questions = ['Q1', 'Q3', 'Q6'];
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
      $feedback_questions = ['Q2', 'Q4', 'Q5'];
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
        ->addValue($constants['Source_Contact_Id'], $constants['SACE_Contact'])
        ->addValue($constants['Booking_Reference_Field'], $booking_activity[0]['id'])
        ->addValue($constants['Activity_Type_Id'], $constants['Feedback_Activity'])
        ->addValue($constants['Activity_Date_Time'], $record['date'])
        ->addValue($constants['Q1_Field'], $activity[$constants['Q1']])
        ->addValue($constants['Q2_Field'], $activity[$constants['Q2']])
        ->addValue($constants['Q3_Field'], $activity[$constants['Q3']])
        ->addValue($constants['Q4_Field'], $activity[$constants['Q4']])
        ->addValue($constants['Q5_Field'], $activity[$constants['Q5']])
        ->addValue($constants['Q6_Field'], $activity[$constants['Q6']])
        ->addValue($constants['Q7_Field'], $activity[$constants['Q7']])
        ->addValue($constants['Q8_Field'], $activity[$constants['Q8']])
        ->execute();

      //Add log
      $result = \Civi\Api4\BookingImportLog::create()
        ->addValue('activity_id', $created[0]["id"])
        ->addValue('table_name', $table)
        ->addValue('legacy_id', $record['legacy_id'])
        ->execute();
        $results[] = $result;
      }
      return $results;
  }

  private static function createEvalActivity($booking, $constants, $record){
    $evals = \Civi\Api4\Activity::create()
      ->addValue($constants['Activity_Type_Id'], $record['activity_type_id'])      
      ->addValue($constants['Source_Contact_Id'], $constants['SACE_Contact'])
      ->addValue($constants['Activity_Date_Time'], $record['date'])
      ->addValue($constants['Eval_Number_Participants'], $record['number_of_participants'])
      ->addValue($constants['Eval_Number_Online_Evals'], 0)
      ->addValue($constants['Eval_Number_Staff_Evals'], $record['number_of_returned_evaluations'])
      ->addValue($constants['Eval_1SA_Field'], $record[$constants['Q1']]['Strongly Agree'])
      ->addValue($constants['Eval_1A_Field'], $record[$constants['Q1']]['Agree'])
      ->addValue($constants['Eval_1SWA_Field'], $record[$constants['Q1']]['Somewhat Agree'])
      ->addValue($constants['Eval_1D_Field'], $record[$constants['Q1']]['Disagree'])
      ->addValue($constants['Eval_1SD_Field'], $record[$constants['Q1']]['Strongly Disagree'])
      ->addValue($constants['Eval_1SUM_Field'], $record[$constants['Q1']]['Strongly Agree']+$record[$constants['Q1']]['Agree']+$record[$constants['Q1']]['Somewhat Agree']+$record[$constants['Q1']]['Disagree']+$record[$constants['Q1']]['Strongly Disagree'])
      ->addValue($constants['Eval_2SUM_Field'], $record[$constants['Q2']])
      ->addValue($constants['Eval_3SA_Field'], $record[$constants['Q3']]['Strongly Agree'])
      ->addValue($constants['Eval_3A_Field'], $record[$constants['Q3']]['Agree'])
      ->addValue($constants['Eval_3SWA_Field'], $record[$constants['Q3']]['Somewhat Agree'])
      ->addValue($constants['Eval_3D_Field'], $record[$constants['Q3']]['Disagree'])
      ->addValue($constants['Eval_3SD_Field'], $record[$constants['Q3']]['Strongly Disagree'])
      ->addValue($constants['Eval_3SUM_Field'], $record[$constants['Q3']]['Strongly Agree']+$record[$constants['Q3']]['Agree']+$record[$constants['Q3']]['Somewhat Agree']+$record[$constants['Q3']]['Disagree']+$record[$constants['Q3']]['Strongly Disagree'])
      ->addValue($constants['Eval_4SUM_Field'], $record[$constants['Q4']])
      ->addValue($constants['Eval_5SA_Field'], $record[$constants['Q5']]['Strongly Agree'])
      ->addValue($constants['Eval_5A_Field'], $record[$constants['Q5']]['Agree'])
      ->addValue($constants['Eval_5SWA_Field'], $record[$constants['Q5']]['Somewhat Agree'])
      ->addValue($constants['Eval_5D_Field'], $record[$constants['Q5']]['Disagree'])
      ->addValue($constants['Eval_5SD_Field'], $record[$constants['Q5']]['Strongly Disagree'])
      ->addValue($constants['Eval_5SUM_Field'], $record[$constants['Q5']]['Strongly Agree']+$record[$constants['Q5']]['Agree']+$record[$constants['Q5']]['Somewhat Agree']+$record[$constants['Q5']]['Disagree']+$record[$constants['Q5']]['Strongly Disagree'])
      ->addValue($constants['Eval_6SUM_Field'], $record[$constants['Q6']])
      ->addValue($constants['Eval_7SA_Field'], $record[$constants['Q7']]['Strongly Agree'])
      ->addValue($constants['Eval_7A_Field'], $record[$constants['Q7']]['Agree'])
      ->addValue($constants['Eval_7SWA_Field'], $record[$constants['Q7']]['Somewhat Agree'])
      ->addValue($constants['Eval_7D_Field'], $record[$constants['Q7']]['Disagree'])
      ->addValue($constants['Eval_7SD_Field'], $record[$constants['Q7']]['Strongly Disagree'])
      ->addValue($constants['Eval_7SUM_Field'], $record[$constants['Q7']]['Strongly Agree']+$record[$constants['Q7']]['Agree']+$record[$constants['Q7']]['Somewhat Agree']+$record[$constants['Q7']]['Disagree']+$record[$constants['Q7']]['Strongly Disagree'])
      ->addValue($constants['Eval_8SA_Field'], $record[$constants['Q8']]['Strongly Agree'])
      ->addValue($constants['Eval_8A_Field'], $record[$constants['Q8']]['Agree'])
      ->addValue($constants['Eval_8SWA_Field'], $record[$constants['Q8']]['Somewhat Agree'])
      ->addValue($constants['Eval_8D_Field'], $record[$constants['Q8']]['Disagree'])
      ->addValue($constants['Eval_8SD_Field'], $record[$constants['Q8']]['Strongly Disagree'])
      ->addValue($constants['Eval_8SUM_Field'], $record[$constants['Q8']]['Strongly Agree']+$record[$constants['Q8']]['Agree']+$record[$constants['Q8']]['Somewhat Agree']+$record[$constants['Q8']]['Disagree']+$record[$constants['Q8']]['Strongly Disagree'])
      ->execute();      
  }

}
