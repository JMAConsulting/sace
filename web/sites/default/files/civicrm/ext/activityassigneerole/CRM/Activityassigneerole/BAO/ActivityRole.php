<?php
// phpcs:disable
use CRM_Activityassigneerole_ExtensionUtil as E;
// phpcs:enable

class CRM_Activityassigneerole_BAO_ActivityRole extends CRM_Activityassigneerole_DAO_ActivityRole {

  /**
   * Create a new ActivityRole based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Activityassigneerole_DAO_ActivityRole|NULL
   */
  /*
  public static function create($params) {
    $className = 'CRM_Activityassigneerole_DAO_ActivityRole';
    $entityName = 'ActivityRole';
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
