<?php

use CRM_Migratepublicedbookings_ExtensionUtil as E;

class CRM_Migratepublicedbookings_Utils {

  public static function importBookings($table) {
    $bookings = CRM_Core_DAO::executeQuery("SELECT * FROM $table")->fetchAll();
    foreach ($bookings as $booking) {
      $record = [];
      //Create Record object with all fields to migrate
      $record['date'] = date_format(date_create("2019-$booking[Month]-$booking[Date]"), 'Y-m-d');
      $record['presentation_topic'] = $booking['Presentation_Topic'];

      //Find staff assigned contact id
      $contacts = \Civi\Api4\Contact::get()
        ->addWhere('contact_type:name', 'CONTAINS', $booking['Presenter_1'])
        // ->addWhere('organization_name', 'CONTAINS', $booking['Organization'])
        ->setLimit(1)
        ->execute();
      $record['staff_assigned_1'] = $contacts[0]['id'];

      if (!empty($booking['Presenter_2'])) {
        $contacts = \Civi\Api4\Contact::get()
          ->addWhere('display_name', 'CONTAINS', $booking['Presenter_2'])
          ->addWhere('organization_name', 'CONTAINS', $booking['Organization'])
          ->setLimit(1)
          ->execute();
        $record['staff_assigned_2'] = $contacts[0]['id'];
      }

      //Presentation Method

      //Organization
      //Check if Organization contact exists
    //   $booking['Organization'] = '12dkj23';
      $organization = \Civi\Api4\Contact::get()
        ->addWhere('contact_type', '=', 'Organization')
        ->addWhere('organization_name', 'CONTAINS', $booking['Organization'])
        ->setLimit(1)
        ->execute();
      if (!empty($organization[0])) {
        $record['organization'] = $organization[0]['id'];
      }
      //create org contact
      else {
        //Create Org Contact
        $results=\Civi\Api4\Contact::create()
          ->addValue('contact_type', 'Organization')
          ->addValue('display_name', $booking['Organization'])
          ->addValue('organization_name', $booking['Organization'])
          ->execute();
          $record['organization'] = $results[0]['id'];
      }

      //Postal Code

      //Activity Duration
      $record['activity_duration'] = $booking['Length'];
      
      //Audience Type Adult or Youth (to determine activity type id)
      if($booking['Audience_Type'] == 'Youth') {
        $record['activity_type_id'] = 57;
      }
      else if($booking['Audience_Type'] == 'Youth') {
        $record['activity_type_id'] = 59;
      }

      //Presentation Population of Focus
    //   $record['presentation_population'] = $booking['Audience_Type2'];

      //Audience
      //   $record['audience'] = $booking['Audience_Type3'];
      return $record;
    }
    // return $bookings;
  }

}
