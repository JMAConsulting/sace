<?php
// phpcs:disable
use CRM_AppointmentNotes_ExtensionUtil as E;
// phpcs:enable

class CRM_AppointmentNotes_BAO_AppointmentNotes extends CRM_AppointmentNotes_DAO_AppointmentNotes {

  /**
   * Create a new AppointmentNotes based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_AppointmentNotes_DAO_AppointmentNotes|NULL
   */
  /*
  public static function create($params) {
  $className = 'CRM_AppointmentNotes_DAO_AppointmentNotes';
  $entityName = 'AppointmentNotes';
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
