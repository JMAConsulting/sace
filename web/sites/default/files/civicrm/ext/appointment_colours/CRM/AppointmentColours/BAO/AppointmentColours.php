<?php
// phpcs:disable
use CRM_AppointmentColours_ExtensionUtil as E;
// phpcs:enable

class CRM_AppointmentColours_BAO_AppointmentColours extends CRM_AppointmentColours_DAO_AppointmentColours {

  /**
   * Create a new AppointmentColours based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_AppointmentColours_DAO_AppointmentColours|NULL
   */
  /*
  public static function create($params) {
  $className = 'CRM_AppointmentColours_DAO_AppointmentColours';
  $entityName = 'AppointmentColours';
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
