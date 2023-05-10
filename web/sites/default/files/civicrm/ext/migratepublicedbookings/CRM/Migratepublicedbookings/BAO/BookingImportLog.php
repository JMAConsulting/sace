<?php
// phpcs:disable
use CRM_Migratepublicedbookings_ExtensionUtil as E;
// phpcs:enable

class CRM_Migratepublicedbookings_BAO_BookingImportLog extends CRM_Migratepublicedbookings_DAO_BookingImportLog {

  /**
   * Create a new BookingImportLog based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Migratepublicedbookings_DAO_BookingImportLog|NULL
   */
  /*
  public static function create($params) {
    $className = 'CRM_Migratepublicedbookings_DAO_BookingImportLog';
    $entityName = 'BookingImportLog';
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
