<?php

use CRM_Pendingmailing_ExtensionUtil as E;

class CRM_Pendingmailing_BAO_Pendingmailing {

  /**
   *
   */
  public static function hasPendingMailing($type = 'mailing') {
    $sql = 'SELECT MAX(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(j.scheduled_date)) as delta
              FROM civicrm_mailing_job as j
              LEFT JOIN civicrm_mailing m ON (m.id = j.mailing_id)
             WHERE j.start_date is NULL
               AND j.scheduled_date IS NOT NULL
               AND j.scheduled_date < NOW()
               AND j.status = %1
               AND j.is_test = 0
               AND m.sms_provider_id ' . ($type == 'mailing' ? 'IS NULL' : 'IS NOT NULL');

    $dao = CRM_Core_DAO::executeQuery($sql, [
      1 => array('Scheduled', 'String'),
    ]);

    if ($dao->fetch()) {
      return $dao->delta;
    }

    return FALSE;
  }

  /**
   *
   *
   */
  public static function elapsedSecondsToHuman($seconds) {
    if ($seconds < 60) {
      return E::ts('%count second', ['plural' => '%count seconds', 'count' => $seconds]);
    }
    elseif ($seconds < 3600) {
      $t = round($seconds/60, 0, PHP_ROUND_HALF_DOWN);
      $t = $t - ($t % 15);
      return E::ts('%count minute', ['plural' => '%count minutes', 'count' => $t]);
    }
    elseif ($seconds < 216000) {
      $t = round($seconds/3600, 0, PHP_ROUND_HALF_DOWN);
      return E::ts('%count hour', ['plural' => '%count hours', 'count' => $t]);
    }
    else {
      $t = round($seconds/216000, 0, PHP_ROUND_HALF_DOWN);
      return E::ts('%count day', ['plural' => '%count days', 'count' => $seconds/216000]);
    }
  }

  /**
   * Helper function to get the Job ID for process_mailing or process_sms.
   */
  static public function getJobId($type) {
    $result = civicrm_api3('Job', 'get', [
      'api_action' => 'process_' . $type,
      'api_entity' => 'Job',
      'sequential' => 1,
    ]);

    if ($result['count'] == 1) {
      return $result['values'][0]['id'];
    }

    return NULL;
  }

  /**
   * Helper function to get the last successful run time from a job.
   */
  static public function getLastRun($job_id) {
    $time = CRM_Core_DAO::singleValueQuery('SELECT run_time FROM civicrm_job_log WHERE job_id = %1 and description like "%finished%" order by id desc limit 1', [
      1 => [$job_id, 'Positive'],
    ]);

    if ($time) {
      $time = substr($time, 0, 16);
    }

    return $time;
  }

}
