<?php
// phpcs:disable
use CRM_Userteamactivity_ExtensionUtil as E;
// phpcs:enable

class CRM_Userteamactivity_BAO_UserTeamActivityEntity extends CRM_Userteamactivity_DAO_UserTeamActivityEntity {

  /**
   * Create a new UserTeamActivityEntity based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Userteamactivity_DAO_UserTeamActivityEntity|NULL
   */
  /*
  public static function create($params) {
    $className = 'CRM_Userteamactivity_DAO_UserTeamActivityEntity';
    $entityName = 'UserTeamActivityEntity';
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
