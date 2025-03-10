<?php
// phpcs:disable
use CRM_Multiplebookingssupport_ExtensionUtil as E;
// phpcs:enable
use Civi\Api4\MultipleBooking;
use Civi\Api4\OptionValue;
use Civi\Api4\Activity;

/**
 * Collection of upgrade steps.
 */
class CRM_Multiplebookingssupport_Upgrader extends CRM_Extension_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   *
   * Note that if a file is present sql\auto_install that will run regardless of this hook.
   */
  // public function install(): void {
  //   $this->executeSqlFile('sql/my_install.sql');
  // }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   */
  // public function postInstall(): void {
  //  $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
  //    'return' => array("id"),
  //    'name' => "customFieldCreatedViaManagedHook",
  //  ));
  //  civicrm_api3('Setting', 'create', array(
  //    'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
  //  ));
  // }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
   * Note that if a file is present sql\auto_uninstall that will run regardless of this hook.
   */
  // public function uninstall(): void {
  //   $this->executeSqlFile('sql/my_uninstall.sql');
  // }

  /**
   * Example: Run a simple query when a module is enabled.
   */
  // public function enable(): void {
  //  CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  // public function disable(): void {
  //   CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws CRM_Core_Exception
   */
  public function upgrade_1000(): bool {
    $this->ctx->log->info('Applying update 1000 Add in booking_calendar_display column and set it appropriately for various activity types');
    $this->addColumn('civicrm_multiple_booking', 'booking_calendar_display', "tinyint NOT NULL DEFAULT 0 COMMENT 'Should this activity type be shown on the booking calendars'");
    $activity_types = [
     'Youth Presentation',
     'Adult Presentation',
     'Youth Online Course',
     'Adult Online Course',
     'Wiseguyz',
     'Booths',
     'Community Engagement',
     'Institutional Support',
     'Consultation',
    ];
    foreach ($activity_types as $activity_type_label) {
      $activityType = OptionValue::get(FALSE)->addWhere('label', '=', $activity_type_label)->addWhere('option_group_id:name', '=', 'activity_type')->execute()->first();
      $check = MultipleBooking::get(FALSE)->addWhere('activity_type_id', '=', $activityType['value'])->execute();
      if (count($check) > 0) {
        MultipleBooking::update(FALSE)->addValue('booking_calendar_display', TRUE)->addWhere('id', '=', $check[0]['id'])->execute();
      }
      else {
        MultipleBooking::create(FALSE)->addValue('activity_type_id', $activityType['value'])->addValue('booking_calendar_display', TRUE)->execute();
      }
    }
    return TRUE;
  }

  public function upgrade_1001(): bool {
    $this->ctx->log->info('Applying update 1001: Populate potentially missing Youth/Adult Field for bookings where applicable');
    $youth_activity_types = [
      'Youth Presentation',
      'Youth Online Course',
      'Wiseguyz',
    ];
    $adult_activity_types = [
      'Adult Presentation',
      'Adult Online Course',
      'Community Engagement',
      'Institutional Support',
      'Consultation',
      'Booths',
    ];
    $youthActivities = Activity::get(FALSE)
      ->addWhere('activity_type_id:label', 'IN', $youth_activity_types)
      ->addWhere('Booking_Information.Youth_or_Adult', 'IS EMPTY')
      ->execute();
    foreach($youthActivities as $youthActivity) {
      Activity::update(FALSE)
        ->addValue('Booking_Information.Youth_or_Adult', 'Youth')
        ->addWhere('id', '=', $youthActivity['id'])
        ->execute();
    }
    $adultActivities = Activity::get(FALSE)
      ->addWhere('activity_type_id:label', 'IN', $adult_activity_types)
      ->addWhere('Booking_Information.Youth_or_Adult', 'IS EMPTY')
      ->execute();
    foreach($adultActivities as $adultActivity) {
      Activity::update(FALSE)
        ->addValue('Booking_Information.Youth_or_Adult', 'Adult')
        ->addWhere('id', '=', $adultActivity['id'])
        ->execute();
    }
    return TRUE;
  }

  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws CRM_Core_Exception
   */
  // public function upgrade_4201(): bool {
  //   $this->ctx->log->info('Applying update 4201');
  //   // this path is relative to the extension base dir
  //   $this->executeSqlFile('sql/upgrade_4201.sql');
  //   return TRUE;
  // }

  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws CRM_Core_Exception
   */
  // public function upgrade_4202(): bool {
  //   $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

  //   $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
  //   $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
  //   $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
  //   return TRUE;
  // }
  // public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  // public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  // public function processPart3($arg5) { sleep(10); return TRUE; }

  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws CRM_Core_Exception
   */
  // public function upgrade_4203(): bool {
  //   $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

  //   $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
  //   $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
  //   for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
  //     $endId = $startId + self::BATCH_SIZE - 1;
  //     $title = E::ts('Upgrade Batch (%1 => %2)', array(
  //       1 => $startId,
  //       2 => $endId,
  //     ));
  //     $sql = '
  //       UPDATE civicrm_contribution SET foobar = apple(banana()+durian)
  //       WHERE id BETWEEN %1 and %2
  //     ';
  //     $params = array(
  //       1 => array($startId, 'Integer'),
  //       2 => array($endId, 'Integer'),
  //     );
  //     $this->addTask($title, 'executeSql', $sql, $params);
  //   }
  //   return TRUE;
  // }

}
