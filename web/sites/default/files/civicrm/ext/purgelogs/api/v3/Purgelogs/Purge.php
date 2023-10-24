<?php

use CRM_Purgelogs_ExtensionUtil as E;
use CRM_Purgelogs_Config as C;
use CRM_Purgelogs_BAO_Purgelogs as Pl;

/**
 * Purgelogs.Purge API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_purgelogs_Purge_spec(&$spec) {
  
}

/**
 * Purgelogs.Purge API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_purgelogs_Purge($params) {

  $initialCalcStats = [];
  $execution_time = microtime(true); // Process duration counter, start of count
  $analysisTable = CRM_Purgelogs_BAO_Purgelogs::Purge();
  $execution_time = microtime(true) - $execution_time;
  $initialCalcStats['execution_time'] = number_format($execution_time, 3);  
  $initialCalcStats['results'] = $analysisTable;

  return civicrm_api3_create_success($initialCalcStats, $params, 'Purgelogs', 'Purge');
}
