<?php

namespace Civi\SACE;

use Civi\Core\Service\AutoSubscriber;

class CalendarFeed extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return [
      'civi.activityical.feed_item_details' => 'onFeedItemDetails',
    ];
  }

  public function onFeedItemDetails(\Civi\Core\Event\GenericHookEvent $e) {
    // add custom fields for SACE
    $extraFields = [
      // already included?
      // Activity subject
      // Start & End Date
      // Type
      // Target
      'CE_External_Activities.Online Meeting Link' => 'Online Meeting Link',
      'CE_External_Activities.Building_Room_Location_details' => 'Building/Room Location',
      'Booking_Information.Parking_Instructions' => 'Parking Instructions',
      'target_contact_id' => 'Target Contact ID',
    ];

    $extraDetails = \Civi\Api4\Activity::get(FALSE)
      ->addWhere('id', '=', $e->activityId)
      ->addSelect(...array_keys($extraFields))
      ->setLimit(1)
      ->execute()
      ->single();

    $extraDescription = $address = [];
    foreach ($extraFields as $field => $label) {
      $value = $extraDetails[$field] ?? NULL;
      if ($field == 'target_contact_id' && !empty($value[0])) {
        $address = \Civi\Api4\Address::get(FALSE)
          ->addSelect('contact_id.display_name', 'street_address', 'city', 'postal_code', 'country_id:label', 'state_province_id:label')
          ->addWhere('contact_id', '=', $value[0])
          ->addWhere('is_primary', '=', TRUE)
          ->setLimit(1)
          ->execute()
          ->single();
      }
      elseif ($value) {
        $extraDescription[] = '<p>' . $label . ': ' . $value . '</p>';
      }
    }

    if ($extraDescription) {
      $extraDescription = implode("\n", $extraDescription);
      $e->row['description'] .= $extraDescription;
    }
    if ($address) {
      unset($address['id']);
      $extraLocation = implode(", ", $address);
      $e->row['location'] .= $extraLocation;
    }
  }

}
