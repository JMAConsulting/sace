<?php
// phpcs:disable
use CRM_Multiplebookingssupport_ExtensionUtil as E;
// phpcs:enable

class CRM_Multiplebookingssupport_BAO_MultipleBooking extends CRM_Multiplebookingssupport_DAO_MultipleBooking {

  /**
   * Create a new MultipleBooking based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Multiplebookingssupport_DAO_MultipleBooking|NULL
   */
  /*
  public static function create($params) {
    $className = 'CRM_Multiplebookingssupport_DAO_MultipleBooking';
    $entityName = 'MultipleBooking';
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
