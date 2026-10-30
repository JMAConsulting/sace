<?php

/**
 * @group headless
 */
class CRM_Cdntaxreceipts_ReceiptsIssuedReportTest extends CRM_Cdntaxreceipts_Base {

  public function setUp(): void {
    parent::setUp();
    $this->setDeliveryMethod(CDNTAX_DELIVERY_PRINT_ONLY);
  }

  public function tearDown(): void {
    $this->_tablesToTruncate = [
      'civicrm_contact',
    ];
    $this->quickCleanUpFinancialEntities();
    parent::tearDown();
  }

  /**
   * Basic test with defaults
   */
  public function testReport() {
    // create contribution
    $contact_id = $this->individualCreate([], 1);
    $datestr = date('Y-m-d');
    $contribution_id = civicrm_api3('Contribution', 'create', [
      'contact_id' => $contact_id,
      'financial_type_id' => 'Donation',
      'total_amount' => '10',
      'receive_date' => $datestr,
    ])['id'];

    // Need it in DAO format
    $contribution = new CRM_Contribute_DAO_Contribution();
    $contribution->id = $contribution_id;
    $contribution->find(TRUE);
    // issue receipt
    list($result, $method) = cdntaxreceipts_issueTaxReceipt($contribution);
    $this->assertTrue($result);
    $this->assertEquals('print', $method);

    // run report
    $data = civicrm_api3('report_template', 'getrows', ['report_id' => 'cdntaxreceipts/receiptsissued'])['values'];

    // We don't care about this and it was introduced in 5.45 so causes fails
    // on earlier matrices.
    unset($data[0]['class']);

    $expected_sort_name = \CRM_Core_BAO_Domain::isDBVersionAtLeast('5.69.alpha1') ? 'Miller, Joe II' : 'Miller, Joe';

    $receipt_no = 'C-' . str_pad($contribution_id, 8, '0', STR_PAD_LEFT);

    // I'm still a little confused about how this passes even when using assertSame.
    // The data types for the 'id' fields are strings in $data, so they
    // shouldn't match when using strict comparison. But is it worth caring
    // about.
    $this->assertEquals([
      [
        'civicrm_contact_sort_name' => $expected_sort_name,
        'civicrm_contact_id' => $contact_id,
        'civicrm_cdntaxreceipts_log_issued_on' => $datestr,
        'civicrm_cdntaxreceipts_log_receipt_amount' => '10.00',
        'civicrm_cdntaxreceipts_log_receipt_no' => $receipt_no,
        'civicrm_cdntaxreceipts_log_issue_type' => 'Single',
        'civicrm_cdntaxreceipts_log_issue_method' => 'Print',
        'civicrm_cdntaxreceipts_log_uid' => 1,
        'civicrm_cdntaxreceipts_log_receipt_status' => 'Issued',
        'civicrm_cdntaxreceipts_log_email_opened' => NULL,
        'civicrm_cdntaxreceipts_log_contributions_contribution_id' => $contribution_id,
        'civicrm_contact_sort_name_link' => '/index.php?q=civicrm/contact/view&amp;reset=1&amp;cid=' . $contact_id,
        'civicrm_contact_sort_name_hover' => 'View Contact Summary for this Contact',
      ],
    ], $data);
  }

  /**
  * Test that the issued_on date range includes and excludes receipts correctly.
  */
  public function testIssuedOnFilter() {
    $contact_id = $this->individualCreate([], 1);

    $contribution_id = civicrm_api3('Contribution', 'create', [
      'contact_id' => $contact_id,
      'financial_type_id' => 'Donation',
      'total_amount' => 10,
      'receive_date' => date('Y-m-d'),
    ])['id'];

    $contribution = new CRM_Contribute_DAO_Contribution();
    $contribution->id = $contribution_id;
    $contribution->find(TRUE);

    list($result) = cdntaxreceipts_issueTaxReceipt($contribution);
    $this->assertTrue($result);

    // The receipt should be returned when today's date is within the range.
    $data = civicrm_api3('report_template', 'getrows', [
      'report_id' => 'cdntaxreceipts/receiptsissued',
      'issued_on_relative' => 0,
      'issued_on_from' => date('Ymd', strtotime('-1 day')),
      'issued_on_to' => date('Ymd', strtotime('+1 day')),
    ])['values'];

    $this->assertCount(1, $data);
    $this->assertEquals(
      date('Y-m-d'),
      $data[0]['civicrm_cdntaxreceipts_log_issued_on']
    );

    // The receipt should not be returned when the range is in the past.
    $data = civicrm_api3('report_template', 'getrows', [
      'report_id' => 'cdntaxreceipts/receiptsissued',
      'issued_on_relative' => 0,
      'issued_on_from' => date('Ymd', strtotime('-1 year')),
      'issued_on_to' => date('Ymd', strtotime('-11 months')),
    ])['values'];

    $this->assertCount(0, $data);
  }

  /**
   * Test optional columns with one line item.
   */
  public function testFinancialTypeSingle() {
    // create contribution
    $contact_id = $this->individualCreate([], 1);
    $datestr = date('Y-m-d');
    $contribution_id = civicrm_api3('Contribution', 'create', [
      'contact_id' => $contact_id,
      'financial_type_id' => 'Donation',
      'total_amount' => '10',
      'receive_date' => $datestr,
    ])['id'];

    // Need it in DAO format
    $contribution = new CRM_Contribute_DAO_Contribution();
    $contribution->id = $contribution_id;
    $contribution->find(TRUE);
    // issue receipt
    list($result, $method) = cdntaxreceipts_issueTaxReceipt($contribution);

    // run report
    $data = civicrm_api3('report_template', 'getrows', [
      'report_id' => 'cdntaxreceipts/receiptsissued',
      'fields' => [
        'sort_name' => 1,
        'issued_on' => 1,
        'receipt_amount' => 1,
        'receipt_no' => $contribution_id,
        'issue_type' => 1,
        'issue_method' => 1,
        'uid' => 1,
        'receipt_status' => 1,
        'email_opened' => 1,
        'contribution_id' => $contribution_id,
        'financial_type_id' => 1,
        'payment_instrument_id' => 1,
      ],
    ])['values'];

    // We don't care about this and it was introduced in 5.45 so causes fails
    // on earlier matrices.
    unset($data[0]['class']);

    $expected_sort_name = \CRM_Core_BAO_Domain::isDBVersionAtLeast('5.69.alpha1') ? 'Miller, Joe II' : 'Miller, Joe';

    $receipt_no = 'C-' . str_pad($contribution_id, 8, '0', STR_PAD_LEFT);

    $this->assertEquals([
      [
        'civicrm_contact_sort_name' => $expected_sort_name,
        'civicrm_contact_id' => $contact_id,
        'civicrm_cdntaxreceipts_log_issued_on' => $datestr,
        'civicrm_cdntaxreceipts_log_receipt_amount' => '10.00',
        'civicrm_cdntaxreceipts_log_receipt_no' => $receipt_no,
        'civicrm_cdntaxreceipts_log_issue_type' => 'Single',
        'civicrm_cdntaxreceipts_log_issue_method' => 'Print',
        'civicrm_cdntaxreceipts_log_uid' => 1,
        'civicrm_cdntaxreceipts_log_receipt_status' => 'Issued',
        'civicrm_cdntaxreceipts_log_email_opened' => NULL,
        'civicrm_cdntaxreceipts_log_contributions_contribution_id' => $contribution_id,
        'civicrm_line_item_financial_type_id' => 'Donation',
        'civicrm_contribution_payment_instrument_id' => 'Check',
        'civicrm_contact_sort_name_link' => '/index.php?q=civicrm/contact/view&amp;reset=1&amp;cid=' . $contact_id,
        'civicrm_contact_sort_name_hover' => 'View Contact Summary for this Contact',
      ],
    ], $data);
  }

}
