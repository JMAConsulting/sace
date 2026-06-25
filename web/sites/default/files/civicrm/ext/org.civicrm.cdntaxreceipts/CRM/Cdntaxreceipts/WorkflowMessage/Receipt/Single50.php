<?php

use Civi\Test;
use Civi\Api4\WorkflowMessage;
use Civi\WorkflowMessage\GenericWorkflowMessage;
use Civi\WorkflowMessage\WorkflowMessageExample;

class CRM_Cdntaxreceipts_WorkflowMessage_Receipt_Single50 extends WorkflowMessageExample {

  public function getExamples(): iterable  {
    yield [
      'name' => 'workflow/cdntaxreceipts_receipt_single/single50',
      'title' => "Single $50",
      'tags' => ['preview'],
      'workflow' => 'cdntaxreceipts_receipt_single',
    ];
  }

  public function build(array &$example): void {
    $workflow = WorkflowMessage::get()->addWhere('name', '=', $example['workflow'])->execute()->first();
    $this->setWorkflowName($workflow['name']);
    /** @var CRM_Cdntaxreceipts_WorkflowMessage_CdntaxreceiptsReceiptSingle $messageTemplate */
    $messageTemplate = new $workflow['class']();
    $contact = Test::example('entity/Contact/Barb');
    $messageTemplate->setContact($contact);
    $messageTemplate->setOrgName(Civi::settings()->get('org_name'));
    $messageTemplate->setOpenTracking('');
    $messageTemplate->setArchiveExtra(['is_archive' => TRUE, 'contact_email' => 'example@example.com']);
    $messageTemplate->setReceipt([
      'receipt_no' => Civi::settings()->get('receipt_prefix') . '00999999',
      'issued_on' => CRM_Cdntaxreceipts_Utils_Time::time(),
      'location_issued' => _cdntaxreceipts_get_location(),
      'contact_id' => $contact['contact_id'],
      'receipt_amount' => '50.00',
      'is_duplicate' => 0,
      'issue_type' => 'single',
      'issue_method' => 'email',
      'receive_date' => date('Y', strtotime(CRM_Cdntaxreceipts_Utils_Time::time())),
      'receipt_status' => 'issued',
      'contributions' => [
        [
          'contribution_id' => 5000,
          'total_amount' => '50.00',
          'receive_date' => CRM_Cdntaxreceipts_Utils_Time::time(),
          'non_deductible_amount' => 0,
        ],
      ],
      'email_tracking_id' => md5(uniqid(rand(), TRUE)),
    ]);
    $example['data'] = $this->toArray($messageTemplate);
  }

}
