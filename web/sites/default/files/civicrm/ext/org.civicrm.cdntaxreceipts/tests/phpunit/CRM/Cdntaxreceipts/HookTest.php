<?php

/**
 * @group headless
 */
class CRM_Cdntaxreceipts_HookTest extends CRM_Cdntaxreceipts_Base {

  private $writeReceiptCount = 0;

  public function tearDown(): void {
    $this->setDeliveryMethod(CDNTAX_DELIVERY_DATA_ONLY);
    parent::tearDown();
  }

  /**
   * Test eligibleAmount
   */
  public function testEligibleAmount() {
    $contact_id = $this->individualCreate([], 0, TRUE);
    $contribution = $this->callAPISuccess('contribution', 'create', [
      'contact_id' => $contact_id,
      'financial_type_id' => 'Donation',
      'total_amount' => '10',
    ]);
    $amount = cdntaxreceipts_eligibleAmount($contribution['id']);
    $this->assertEquals(10, $amount);
  }

  /**
   * Test eligibleAmount with hook
   * @see hookForEligibleAmount
   */
  public function testEligibleAmountWithHook() {
    \Civi::dispatcher()->addListener('hook_cdntaxreceipts_eligibleAmount', [$this, 'hookForEligibleAmount']);
    $contact_id = $this->individualCreate([], 0, TRUE);
    $contribution = $this->callAPISuccess('contribution', 'create', [
      'contact_id' => $contact_id,
      'financial_type_id' => 'Donation',
      'total_amount' => '10',
    ]);
    // We're expecting our hook to subtract 5 from the amount.
    $amount = cdntaxreceipts_eligibleAmount($contribution['id']);
    $this->assertEquals(5, $amount);
  }

  /**
   * This is the listener for hook_cdntaxreceipts_eligibleAmount
   *
   * @param \Civi\Core\Event\GenericHookEvent $e
   *   has member CRM_Contribute_DAO_Contribution $contribution
   */
  public function hookForEligibleAmount(\Civi\Core\Event\GenericHookEvent $e) {
    // alter the existing amount by subtracting 5
    $e->addReturnValues([$e->contribution->total_amount - 5]);
  }

  public function testWriteReceiptWithHook(): void {
    // It needs print since the hook isn't called for method=data
    $this->setDeliveryMethod(CDNTAX_DELIVERY_PRINT_ONLY);
    \Civi::dispatcher()->addListener('hook_cdntaxreceipts_writeReceipt', [$this, 'hookForWriteReceipt']);

    $contact_id = $this->individualCreate([], 3);
    $contribution_id = $this->callAPISuccess('Contribution', 'create', [
      'contact_id' => $contact_id,
      'financial_type_id' => 'Donation',
      'total_amount' => '10',
    ])['id'];
    // Need it in DAO format
    $contribution = new CRM_Contribute_DAO_Contribution();
    $contribution->id = $contribution_id;
    $contribution->find(TRUE);
    // issue receipt
    cdntaxreceipts_issueTaxReceipt($contribution);
    $this->assertEquals(1, $this->writeReceiptCount);

    // Now do again with customized greeting
    civicrm_api3('Contact', 'create', [
      'id' => $contact_id,
      'postal_greeting_custom' => 'Yo {contact.last_name}',
    ]);
    $contribution_id = $this->callAPISuccess('Contribution', 'create', [
      'contact_id' => $contact_id,
      'financial_type_id' => 'Donation',
      'total_amount' => '20',
    ])['id'];
    $contribution = new CRM_Contribute_DAO_Contribution();
    $contribution->id = $contribution_id;
    $contribution->find(TRUE);
    cdntaxreceipts_issueTaxReceipt($contribution);
    $this->assertEquals(2, $this->writeReceiptCount);
  }

  /**
   * This is the listener for hook_cdntaxreceipts_writeReceipt
   *
   * @param \Civi\Core\Event\GenericHookEvent $e
   */
  public function hookForWriteReceipt(\Civi\Core\Event\GenericHookEvent $e) {
    switch (++$this->writeReceiptCount) {
      case 1:
        $this->assertEquals('Dear Lucie', $e->pdf_variables['postal_greeting']);
        break;

      case 2:
        $this->assertEquals('Yo Collins', $e->pdf_variables['postal_greeting']);
        break;

      default:
        $this->fail('Should only be here with count 1 or 2');
    }
    $e->addReturnValues([TRUE]);
  }

}
