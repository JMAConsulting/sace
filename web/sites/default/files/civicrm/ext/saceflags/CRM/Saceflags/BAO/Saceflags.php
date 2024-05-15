<?php
// phpcs:disable
use CRM_Saceflags_ExtensionUtil as E;
// phpcs:enable

class CRM_Saceflags_BAO_Saceflags extends CRM_Saceflags_DAO_Saceflags {

  /**
   * Create a new Saceflags based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Saceflags_DAO_Saceflags|NULL
   */
  /*
  public static function create($params) {
  $className = 'CRM_Saceflags_DAO_Saceflags';
  $entityName = 'Saceflags';
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
