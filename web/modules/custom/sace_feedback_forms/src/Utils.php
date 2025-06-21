<?php

namespace Drupal\sace_feedback_forms;

use Drupal\webform\Utility\WebformFormHelper;

class Utils {

  protected static $fieldNameCache = [];

  public static function getWebformFieldForCustomField(string $customFieldName, string $customGroupName, string $entity, int $entityIndex = 1, int $cgIndex = 1) : string {
    $field = \Civi\Api4\CustomField::get(FALSE)
      ->addWhere('name', '=', $customFieldName)
      ->addWhere('custom_group_id.name', '=', $customGroupName)
      ->addSelect('id', 'custom_group_id', 'extends')
      ->execute()
      ->single();

    return "civicrm_{$entityIndex}_{$entity}_{$cgIndex}_cg{$field['custom_group_id']}_custom_{$field['id']}";
  }

  public static function getFeedbackCustomFieldsForBooking($bookingId): array {
    $formKey = self::getFeedbackFormForBooking($bookingId);
    if (!$formKey) {
      return [];
    }
    $webform = \Drupal::entityTypeManager()->getStorage('webform')->load($formKey);
    // check which fields have been added as form elements
    $elements = array_keys($webform->getElementsDecodedAndFlattened());

    $customFieldIds = array_filter(array_map(function ($element) {
      if (\str_starts_with($element, 'civicrm_1_activity_1_cg')) {
        $elementParts = explode('_', $element);
        if ($elementParts[5] === 'custom') {
          return (int) $elementParts[6];
        }
      }
    }, $elements));

    return (array) \Civi\Api4\CustomField::get(FALSE)
      ->addWhere('id', 'IN', $customFieldIds)
      ->addSelect('*', 'custom_group_id.name')
      // TODO: exclude fields that always appear in the header?
      ->addWhere('custom_group_id.name', 'NOT IN', ['Booking_Information'])
      ->addWhere('name', '!=', 'Booking')
      ->execute();
  }

  public static function getFeedbackFormForBooking($bookingId): ?string {
    return \Civi\Api4\Activity::get(FALSE)
      ->addWhere('id', '=', $bookingId)
      ->addSelect('Booking_Information.Feedback_Webform')
      ->execute()
      ->first()['Booking_Information.Feedback_Webform'] ?? NULL;
  }

  public static function addElementOrSetDefault(&$elements, $key, $config)
  {
    $flattenedElements = WebformFormHelper::flattenElements($elements);
    if (isset($flattenedElements[$key])) {
      if (isset($config['#default_value'])) {
        $flattenedElements[$key]['#default_value'] = $config['#default_value'];
      }
    } else {
      $elements[$key] = $config;
    }
  }

}
