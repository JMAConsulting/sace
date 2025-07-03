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
    $extraDescriptionFields = [
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

    $details = \Civi\Api4\Activity::get(FALSE)
      ->addWhere('id', '=', $e->activityId)
      ->addSelect(...array_keys($extraDescriptionFields))
      // we also add location for target contact id
      ->addSelect('target_contact_id')
      ->setLimit(1)
      ->execute()
      ->single();

    $extraDescription = [];
    foreach ($extraDescriptionFields as $field => $label) {
      $value = $details[$field] ?? NULL;
      if ($value) {
        $extraDescription[] = '<p>' . $label . ': ' . $value . '</p>';
      }
    }

    if ($extraDescription) {
      $extraDescription = implode("\n", $extraDescription);
      $e->row['description'] .= $extraDescription;
    }

    $targetContactId = $details['target_contact_id'][0] ?? NULL;

    if (!$e->row['address_location'] && $targetContactId) {
      $address = \Civi\Api4\Address::get(FALSE)
        ->addSelect('contact_id.display_name', 'street_address', 'city', 'postal_code', 'country_id:label', 'state_province_id:label')
        ->addWhere('contact_id', '=', $targetContactId)
        ->addWhere('is_primary', '=', TRUE)
        ->setLimit(1)
        ->execute()
        ->first();

      if ($address) {
        unset($address['id']);
        $e->row['address_location'] = implode(", ", $address);
      }
    }
  }

}
