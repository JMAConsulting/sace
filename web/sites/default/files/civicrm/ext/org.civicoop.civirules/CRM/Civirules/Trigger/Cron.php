<?php

abstract class CRM_Civirules_Trigger_Cron extends CRM_Civirules_Trigger {

  /**
   * @var \CRM_Core_Lock
   */
  private $lock;

  /**
   * This function returns a CRM_Civirules_TriggerData_TriggerData this entity is used for triggering the rule
   *
   * Return false when no next entity is available
   *
   * @return CRM_Civirules_TriggerData_TriggerData|false
   */
  abstract protected function getNextEntityTriggerData();

  /**
   * @return array
   */
  public function process() {
    $count = 0;
    $isValidCount = 0;

    if (!$this->acquireLock()) {
      return array(
        'count' => $count,
        'is_valid_count' => $isValidCount,
      );
    }

    while($triggerData = $this->getNextEntityTriggerData()) {
      $this->alterTriggerData($triggerData);
      $isValid = CRM_Civirules_Engine::triggerRule($this, $triggerData);
      if ($isValid) {
        $isValidCount++;
      }
      $count ++;
    }

    $this->releaseLock();

    return array(
      'count' => $count,
      'is_valid_count' => $isValidCount,
    );
  }

  /**
   * Acquires a lock. Returns true when the lock was free and is acquired
   * Returns false when the lock was not free or could not be acquired.
   *
   * @return bool
   */
  protected function acquireLock() {
    try {
      $name = 'civirules_cron_rule_' . $this->getRuleId();
      if ($this->lock == NULL) {
        $this->lock = new CRM_Core_Lock($name, $this->getLockTimeout());
        if ($this->lock->isFree()) {
          $this->lock->acquire();
          if ($this->lock->isAcquired()) {
            return TRUE;
          }
        }
      }
    } catch (\CRM_Core_Exception $e) {
      // Do nothing.
    }
    return FALSE;
  }

  /**
   * Releases the lock
   *
   */
  protected function releaseLock() {
    if ($this->lock) {
      $this->lock->release();
    }
  }

  /**
   * Returns the lock timeout for this trigger in seconds
   *
   * @return int
   */
  protected function getLockTimeout() {
    return 1800; //1800 seconds = 30 minutes
  }

  /*
   * Returns the name of the trigger data class.
   *
   * This function could be overridden in a child class.
   *
   * @return String
   */
  public function getTriggerDataClassName() {
    return 'CRM_Civirules_TriggerData_Cron';
  }


}
