<?php
// phpcs:disable
use CRM_ActivityTypeProgram_ExtensionUtil as E;
// phpcs:enable

class CRM_ActivityTypeProgram_BAO_ActivityTypeProgram extends CRM_ActivityTypeProgram_DAO_ActivityTypeProgram {

  /**
   * Create a new ActivityTypeProgram based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_ActivityTypeProgram_DAO_ActivityTypeProgram|NULL
   */
  /*
  public static function create($params) {
  $className = 'CRM_ActivityTypeProgram_DAO_ActivityTypeProgram';
  $entityName = 'ActivityTypeProgram';
  $hook = empty($params['id']) ? 'create' : 'edit';

  CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
  $instance = new $className();
  $instance->copyValues($params);
  $instance->save();
  CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

  return $instance;
  }
   */

}
