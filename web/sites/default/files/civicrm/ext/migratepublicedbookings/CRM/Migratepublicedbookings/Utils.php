<?php

use CRM_Migratepublicedbookings_ExtensionUtil as E;

class CRM_Migratepublicedbookings_Utils {

  public static function importBookings($table) {
    $bookings = CRM_Core_DAO::executeQuery("SELECT * FROM $table")->fetchAll();
    foreach ($bookings as $booking) {
      $record = [];
      //    Create Record object with all fields to migrate
      //    Activity Date
      $record['date'] = date_format(date_create("2019-$booking[Month]-$booking[Date]"), 'Y-m-d');

      //    Presentation Topic
      $record['presentation_topic'] = $booking['Presentation_Topic'];

      //    Find staff assigned contact id
      $contacts = \Civi\Api4\Contact::get()
        ->addWhere('contact_type:name', 'CONTAINS', $booking['Presenter_1'])
        // ->addWhere('organization_name', 'CONTAINS', $booking['Organization'])
        ->setLimit(1)
        ->execute();
      $record['staff_assigned_1'] = $contacts[0]['id'];

      if (!empty($booking['Presenter_2'])) {
        $contacts = \Civi\Api4\Contact::get()
          ->addWhere('display_name', 'CONTAINS', $booking['Presenter_2'])
          ->setLimit(1)
          ->execute();
        $record['staff_assigned_2'] = $contacts[0]['id'];
      }

      if (!empty($booking['Presenter_3'])) {
        $contacts = \Civi\Api4\Contact::get()
          ->addWhere('display_name', 'CONTAINS', $booking['Presenter_3'])
          ->setLimit(1)
          ->execute();
        $record['staff_assigned_3'] = $contacts[0]['id'];
      }

      //    Presentation Method
      //if year between 2014 - 2019, In Person
      $record['presentation_method'] = "In-Person Presentation";

      //    Postal Code
      $record['postal_code'] = $booking['Postal_Code'];

      //    Organization
      //    Check if Organization contact exists
      $organization = \Civi\Api4\Contact::get()
        ->addWhere('contact_type', '=', 'Organization')
        ->addWhere('organization_name', 'CONTAINS', $booking['Organization'])
        ->setLimit(1)
        ->execute();
      if (!empty($organization[0])) {
        $record['organization'] = $organization[0]['id'];
      }
      //  Create Org Contact
      else {
        $results = \Civi\Api4\Contact::create()
          ->addValue('contact_type', 'Organization')
          ->addValue('display_name', $booking['Organization'])
          ->addValue('organization_name', $booking['Organization'])
          ->execute();
        $record['organization'] = $results[0]['id'];

        //  Add postal code for the created Organization
        \Civi\Api4\Address::create()
          ->addValue('postal_code', $record['postal_code'])
          ->addValue('contact_id', $record['organization'])
          ->execute();
      }

      //    Activity Duration
      $record['activity_duration'] = $booking['Length'];

      //    Audience Type Adult or Youth (to determine activity type id)
      if ($booking['Audience_Type'] == 'Youth') {
        $record['activity_type_id'] = 57;
      }
      elseif ($booking['Audience_Type'] == 'Youth') {
        $record['activity_type_id'] = 59;
      }

      //    Number of Participants
      $record['number_of_participants'] = $booking['Number_of_Participants'];

      //    Number of Returned Evals
      $record['number_of_returned_evaluations'] = $booking['Number_of_Returned Evaluations'];

      //Presentation Population of Focus
      //   $record['presentation_population'] = $booking['Audience_Type2'];

      //    Audience
      //   $record['audience'] = $booking['Audience_Type3'];

      //  Q1
      $record["q1"] = [
        "strongly_agree" => $booking['STRONGLY_AGREE1'],
        "agree" => $booking['AGREE1'],
        "dont_know" => $booking['DONT_KNOW1'],
        "disagree" => $booking['DISAGREE1'],
        "strongly_disagree" => $booking['STRONGLY_ DISAGREE1'],
      ];

      //  Q2
      $record["q2"] = $booking["Number_Surveyed_Able_to_List_Something_Learned"];

      //  Q3
      $record["q4"] = [
        "strongly_agree" => $booking['STRONGLY_AGREE3'],
        "agree" => $booking['AGREE3'],
        "dont_know" => $booking['DONT_KNOW3'],
        "disagree" => $booking['DISAGREE3'],
        "strongly_disagree" => $booking['STRONGLY_DISAGREE3'],
      ];

      //  Q4
      $record["q5"] = $booking["Number_Surveyed_Able_to_List_Something_To_Do_to_Support"];

      //  Q5
      $record["q8"] = [
        "strongly_agree" => $booking['STRONGLY_AGREE5'],
        "agree" => $booking['AGREE5'],
        "dont_know" => $booking['DONT_KNOW5'],
        "disagree" => $booking['DISAGREE5'],
        "strongly_disagree" => $booking['STRONGLY_DISAGREE5'],
      ];

      //  Q6
      $record["q9"] = $booking["Number_Surveyed_Able_to_List_a_Resource"];

      //  Q7
      $record["q11"] = [
        "strongly_agree" => $booking['STRONGLY_AGREE7'],
        "agree" => $booking['AGREE7'],
        "dont_know" => $booking['DONT_KNOW7'],
        "disagree" => $booking['DISAGREE7'],
        "strongly_disagree" => $booking['STRONGLY_DISAGREE7'],
      ];

      //  Q8
      $record["q12"] = [
        "strongly_agree" => $booking['STRONGLY_AGREE8'],
        "agree" => $booking['AGREE8'],
        "dont_know" => $booking['DONT_KNOW8'],
        "disagree" => $booking['DISAGREE8'],
        "strongly_disagree" => $booking['STRONGLY_DISAGREE8'],
      ];

      $results = \Civi\Api4\Activity::create()
        ->addValue('activity_type_id', 197)
        ->addValue('PED_Participant_Presentation_Feedback.q1', 'Strongly Agree')
        ->addValue('PED_Participant_Presentation_Feedback.feedback', 'test')
        ->addValue('source_contact_id', 'user_contact_id')
        ->addValue('PED_Participant_Presentation_Feedback.q3', 'Strongly Agree')
        ->execute();
      foreach ($results as $result) {
        // do something
      }

      return $record;
    }
    // return $bookings;
  }

}
