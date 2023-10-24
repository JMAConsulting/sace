<?php
use CRM_Purgelogs_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Purgelogs_Upgrader extends CRM_Purgelogs_Upgrader_Base {

  public function install() {
    $this->createScheduledJob();
  }

  /**
   * Creates 'Renew offline auto-renewal memberships'
   * Scheduled Job.
   */
  private function createScheduledJob() {
    $result = civicrm_api3('Job', 'get', [
      'name' => 'Purge Logs',
    ]);

    if ($result['count'] > 0) {
      return;
    }

    civicrm_api3('Job', 'create', [
      'run_frequency' => 'Daily',
      'name' => 'Purge Logs',
      'description' => ts('Automatically remove old log files'),
      'api_entity' => 'purgelogs',
      'api_action' => 'purge',
      'is_active' => 0,
    ]);
  }

}
