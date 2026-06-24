<?php

use Civi\WorkflowMessage\GenericWorkflowMessage;

/**
 * Annaul/Aggregate Invoice message
 *
 * @method array getArchiveExtra()
 * @method array getReceipt()
 * @method string getOrgName()
 * @method string getOpenTracking
 *
 * @support template-only
 */
class CRM_Cdntaxreceipts_WorkflowMessage_CdntaxreceiptsReceiptAggregate extends GenericWorkflowMessage {

  public const WORKFLOW = 'cdntaxreceipts_receipt_aggregate';

  /**
   * @var array
   * @scope tplParams
   */
  protected $receipt;

  /**
   * @var array
   * @scope tplParams
   */
  protected $archive_extra;

  /**
   * @var string
   * @scope tplParams
   */
  protected $openTracking;

  /**
   * @var string
   * @scope tplParams
   */
  protected $orgName;


  public function setArchiveExtra(array $archiveExtra) {
    $this->archive_extra = $archiveExtra;
    return $this;
  }

  public function setReceipt(array $receipt) {
    $this->receipt = $receipt;
    return $this;
  }

  public function setOrgName(string $orgName) {
    $this->orgName = $orgName;
    return $this;
  }

  public function setOpenTracking(string $openTracking) {
    $this->openTracking = $openTracking;
    return $this;
  }

}
