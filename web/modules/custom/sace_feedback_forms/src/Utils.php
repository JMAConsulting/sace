<?php

namespace Drupal\sace_feedback_forms;

class Utils {

  public static function getWebformFieldForCustomField(string $customFieldName, string $customGroupName, string $entity, int $entityIndex = 1, int $cgIndex = 1) : string {
    $field = \Civi\Api4\CustomField::get(FALSE)
      ->addWhere('name', '=', $customFieldName)
      ->addWhere('custom_group_id.name', '=', $customGroupName)
      ->addSelect('id', 'custom_group_id', 'extends')
      ->execute()
      ->single();

    return "civicrm_{$entityIndex}_{$entity}_{$cgIndex}_1_cg{$field['custom_group_id']}_custom_{$field['id']}";
  }
}
