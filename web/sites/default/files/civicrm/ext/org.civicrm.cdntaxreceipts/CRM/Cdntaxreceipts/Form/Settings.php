<?php

use CRM_Cdntaxreceipts_ExtensionUtil as E;
require_once 'CRM/Core/Form.php';
require_once E::path('cdntaxreceipts.functions.inc');

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Cdntaxreceipts_Form_Settings extends CRM_Core_Form {

  CONST SETTINGS = 'CDNTaxReceipts';

  protected $unsavedChangesWarn = TRUE;

  function buildQuickForm() {

    CRM_Utils_System::setTitle(ts('Configure CDN Tax Receipts', ['domain' => 'org.civicrm.cdntaxreceipts']));
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.cdntaxreceipts', 'css/civicrm_cdntaxreceipts.css');

    $this->processOrgOptions('build');
    $this->processReceiptOptions('build');
    $this->processSystemOptions('build');
    $this->processEmailOptions('build');

    $arr1 = $this->processOrgOptions('defaults');
    $arr2 = $this->processReceiptOptions('defaults');
    $arr3 = $this->processSystemOptions('defaults');
    $arr4 = $this->processEmailOptions('defaults');
    $defaults = array_merge($arr1, $arr2, $arr3, $arr4);
    $this->setDefaults($defaults);

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => ts('Submit', ['domain' => 'org.civicrm.cdntaxreceipts']),
        'isDefault' => TRUE,
      ],
    ]);
    // Set image defaults
    $images = ['receipt_logo', 'receipt_signature', 'receipt_watermark', 'receipt_pdftemplate'];
    foreach ($images as $image) {
      $this->assign($image, NULL);
      $this->assign($image.'_class', FALSE);
      if (!empty($defaults[$image])) {
        $this->assign($image, $defaults[$image]);
        if (!file_exists(CRM_Core_Config::singleton()->customFileUploadDir . $defaults[$image])) {
          // @todo: why is this variable called _class?
          $this->assign($image.'_class', TRUE);
        }
      }
    }

    parent::buildQuickForm();
  }

  function processOrgOptions($mode) {
    if ( $mode == 'build' ) {
      $this->add('text', 'org_name', ts('Organization Name', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->add('text', 'org_address_line1', ts('Address Line 1', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->add('text', 'org_address_line2', ts('Address Line 2', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->add('text', 'org_tel', ts('Telephone', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->add('text', 'org_fax', ts('Fax', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->add('text', 'org_email', ts('Email', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->add('text', 'org_web', ts('Website', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->add('text', 'org_charitable_no', ts('Charitable Registration Number', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->add('text', 'receipt_location_issued', ts('Location Issued', ['domain' => 'org.civicrm.cdntaxreceipts']));

      // @todo: Do we need to do this - can't we just use the `required` param in add() above?
      // If we do need to do this, shouldn't the labels be ts'd?
      $this->addRule('org_name', 'Enter Organization Name', 'required');
      $this->addRule('org_address_line1', 'Enter Address Line 1', 'required');
      $this->addRule('org_address_line2', 'Enter Address Line 2', 'required');
      $this->addRule('org_tel', 'Enter Telephone', 'required');
      $this->addRule('org_email', 'Enter Email', 'required');
      $this->addRule('org_web', 'Enter Website', 'required');
      $this->addRule('org_charitable_no', 'Enter Charitable Number', 'required');
      $this->addRule('receipt_location_issued', 'Location Issued', 'required');
    }
    else if ( $mode == 'defaults' ) {
      $defaults = [
        'org_name' => Civi::settings()->get('org_name'),
        'org_address_line1' => Civi::settings()->get('org_address_line1'),
        'org_address_line2' => Civi::settings()->get('org_address_line2'),
        'org_tel' => Civi::settings()->get('org_tel'),
        'org_fax' => Civi::settings()->get('org_fax'),
        'org_email' => Civi::settings()->get('org_email'),
        'org_web' => Civi::settings()->get('org_web'),
        'receipt_logo' => Civi::settings()->get('receipt_logo'),
        'receipt_signature' => Civi::settings()->get('receipt_signature'),
        'receipt_watermark' => Civi::settings()->get('receipt_watermark'),
        'receipt_pdftemplate' => Civi::settings()->get('receipt_pdftemplate'),
        'org_charitable_no' => Civi::settings()->get('org_charitable_no'),
        'receipt_location_issued' => Civi::settings()->get('receipt_location_issued'),
      ];
      return $defaults;
    }
    else if ( $mode == 'post' ) {
      $values = $this->exportValues();
      Civi::settings()->set('org_name', $values['org_name']);
      Civi::settings()->set('org_address_line1', $values['org_address_line1']);
      Civi::settings()->set('org_address_line2', $values['org_address_line2']);
      Civi::settings()->set('org_tel', $values['org_tel']);
      Civi::settings()->set('org_fax', $values['org_fax']);
      Civi::settings()->set('org_email', $values['org_email']);
      Civi::settings()->set('org_web', $values['org_web']);
      Civi::settings()->set('org_charitable_no', $values['org_charitable_no']);
      Civi::settings()->set('receipt_location_issued', $values['receipt_location_issued']);
    }

  }

  function processReceiptOptions($mode) {
    if ( $mode == 'build' ) {
      $this->add('text', 'receipt_prefix', ts('Receipt Prefix', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->add('text', 'receipt_authorized_signature_text', ts('Authorized Signature Text', ['domain' => 'org.civicrm.cdntaxreceipts']));

      $uploadSize = cdntaxreceipts_getCiviSetting('maxFileSize');
      if ($uploadSize >= 8 ) {
        $uploadSize = 8;
      }
      $uploadFileSize = $uploadSize * 1024 * 1024;

      $this->assign('uploadSize', $uploadSize );
      $this->setMaxFileSize( $uploadFileSize );

      $this->addElement('file', 'receipt_logo', ts('Organization Logo', ['domain' => 'org.civicrm.cdntaxreceipts']), 'size=30 maxlength=60');
      $this->addUploadElement('receipt_logo');
      $this->addRule( 'receipt_logo', ts('File size should be less than %1 MBytes (%2 bytes)', [1 => $uploadSize, 2 => $uploadFileSize]), 'maxfilesize', $uploadFileSize, ['domain' => 'org.civicrm.cdntaxreceipts'] );

      $this->addElement('file', 'receipt_signature', ts('Signature Image', ['domain' => 'org.civicrm.cdntaxreceipts']), 'size=30 maxlength=60');
      $this->addUploadElement('receipt_signature');
      $this->addRule( 'receipt_signature', ts('File size should be less than %1 MBytes (%2 bytes)', [1 => $uploadSize, 2 => $uploadFileSize]), 'maxfilesize', $uploadFileSize, ['domain' => 'org.civicrm.cdntaxreceipts'] );

      $this->addElement('file', 'receipt_watermark', ts('Watermark Image', ['domain' => 'org.civicrm.cdntaxreceipts']), 'size=30 maxlength=60');
      $this->addUploadElement('receipt_watermark');
      $this->addRule( 'receipt_watermark', ts('File size should be less than %1 MBytes (%2 bytes)', [1 => $uploadSize, 2 => $uploadFileSize]), 'maxfilesize', $uploadFileSize, ['domain' => 'org.civicrm.cdntaxreceipts'] );

      $this->addElement('file', 'receipt_pdftemplate', ts('PDF Template', ['domain' => 'org.civicrm.cdntaxreceipts']), 'size=30 maxlength=60');
      $this->addUploadElement('receipt_pdftemplate');
      $this->addRule( 'receipt_pdftemplate', ts('File size should be less than %1 MBytes (%2 bytes)', [1 => $uploadSize, 2 => $uploadFileSize]), 'maxfilesize', $uploadFileSize, ['domain' => 'org.civicrm.cdntaxreceipts'] );

      $tokenProcessor = new \Civi\Token\TokenProcessor(Civi::dispatcher(), ['schema' => ['contactId', 'contributionId']]);
      $source_field_tokens = $tokenProcessor->listTokens();
      $this->assign('tokens', CRM_Utils_Token::formatTokensForDisplay($source_field_tokens));
      $this->addElement('text', 'source_field', ts('Source Field Value', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->addElement('text', 'source_label', ts('Source Field Label (%1)', [1 => CRM_Core_I18n::getLocale(), 'domain' => 'org.civicrm.cdntaxreceipts']));
    }
    else if ( $mode == 'defaults' ) {
      $defaults = [
        'receipt_prefix' => Civi::settings()->get('receipt_prefix'),
        'receipt_authorized_signature_text' => Civi::settings()->get('receipt_authorized_signature_text'),
        'source_field' => Civi::settings()->get('cdntaxreceipts_source_field') ?? '',
        'source_label' => Civi::settings()->get('cdntaxreceipts_source_label_' . CRM_Core_I18n::getLocale()) ?? '',
      ];
      return $defaults;
    }
    else if ( $mode == 'post' ) {
      $values = $this->exportValues();
      Civi::settings()->set('receipt_prefix', $values['receipt_prefix']);
      Civi::settings()->set('receipt_authorized_signature_text', $values['receipt_authorized_signature_text']);
      Civi::settings()->set('cdntaxreceipts_source_field', $values['source_field']);
      Civi::settings()->set('cdntaxreceipts_source_label_' . CRM_Core_I18n::getLocale(), $values['source_label']);

      foreach ( ['receipt_logo', 'receipt_signature', 'receipt_watermark', 'receipt_pdftemplate'] as $key ) {
        $upload_file = $this->getSubmitValue($key);
        if (is_array($upload_file)) {
          if ( $upload_file['error'] == 0 ) {
            $filename = CRM_Utils_File::makeFileName($upload_file['name']);
            if (!move_uploaded_file($upload_file['tmp_name'], CRM_Core_Config::singleton()->customFileUploadDir . $filename)) {
              CRM_Core_Error::fatal(ts('Could not upload the file'));
            }
            Civi::settings()->set($key, $filename);
          }
        }
      }
    }
  }

  function processSystemOptions($mode) {
    if ( $mode == 'build' ) {
      $this->addElement('checkbox', 'issue_inkind', ts('Setup in-kind receipts?', ['domain' => 'org.civicrm.cdntaxreceipts']));
      // We may not know the financial type, but maybe the custom fields are present.
      $this->assign('inkind_financial_type_is_known', (bool) Civi::settings()->get('cdntaxreceipts_inkind'));
      $inkind_custom = \Civi\Api4\CustomGroup::get(FALSE)
        ->addSelect('id')
        ->addWhere('name', '=', 'In_kind_donation_fields')
        ->execute()->first();
      $this->assign('has_inkind_custom', !empty($inkind_custom['id']));
      $this->addEntityRef('inkind_financial_type', ts('In-kind Financial Type', ['domain' => 'org.civicrm.cdntaxreceipts']), ['entity' => 'FinancialType']);

      $delivery_options = [];
      $delivery_options[] = $this->createElement('radio', NULL, NULL, 'Print only', CDNTAX_DELIVERY_PRINT_ONLY);
      $delivery_options[] = $this->createElement('radio', NULL, NULL, 'Email or print', CDNTAX_DELIVERY_PRINT_EMAIL);
      $delivery_options[] = $this->createElement('radio', NULL, NULL, 'Data only', CDNTAX_DELIVERY_DATA_ONLY);
      $this->addGroup($delivery_options, 'delivery_method', ts('Delivery Method', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->addRule('delivery_method', 'Delivery Method', 'required');

      $yesno_options = [];
      $yesno_options[] = $this->createElement('radio', NULL, NULL, 'Yes', 1);
      $yesno_options[] = $this->createElement('radio', NULL, NULL, 'No', 0);
      $this->addGroup($yesno_options, 'attach_to_workflows', ts('Attach receipts to automated workflow messages?', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->addRule('attach_to_workflows', 'Attach tax receipts to automated messages', 'required');

      $yesno_options2 = [];
      $yesno_options2[] = $this->createElement('radio', NULL, NULL, 'Yes', 1);
      $yesno_options2[] = $this->createElement('radio', NULL, NULL, 'No', 0);
      $this->addGroup($yesno_options2, 'enable_advanced_eligibility_report', ts('Enable Advanced Eligibility Check?', ['domain' => 'org.civicrm.cdntaxreceipts']));
    }
    else if ( $mode == 'defaults' ) {
      $defaults = [
        'issue_inkind' => 0,
        'inkind_financial_type' => Civi::settings()->get('cdntaxreceipts_inkind') ?? 0,
        'delivery_method' => Civi::settings()->get('delivery_method') ?? CDNTAX_DELIVERY_PRINT_ONLY,
        'attach_to_workflows' => Civi::settings()->get('attach_to_workflows') ?? 0,
        'enable_advanced_eligibility_report' => Civi::settings()->get('enable_advanced_eligibility_report') ?? 0,
      ];
      return $defaults;
    }
    else if ( $mode == 'post' ) {
      $values = $this->exportValues();
      Civi::settings()->set('delivery_method', $values['delivery_method']);
      Civi::settings()->set('attach_to_workflows', $values['attach_to_workflows']);
      Civi::settings()->set('enable_advanced_eligibility_report', $values['enable_advanced_eligibility_report']);
      if (!empty($values['inkind_financial_type'])) {
        Civi::settings()->set('cdntaxreceipts_inkind', $values['inkind_financial_type']);
      }
      if (isset($values['issue_inkind']) == TRUE) {
        if ( $values['issue_inkind'] == 1 ) {
          cdntaxreceipts_configure_inkind_fields();
        }
      }
    }
  }

  function processEmailOptions($mode) {
    if ( $mode == 'build' ) {
      $this->add('text', 'email_from', ts('Email From', ['domain' => 'org.civicrm.cdntaxreceipts']));
      $this->add('text', 'email_archive', ts('Archive Email', ['domain' => 'org.civicrm.cdntaxreceipts']));

      $this->addRule('email_from', 'Enter email from address', 'required');
      $this->addRule('email_archive', 'Enter email archive address', 'required');
    }
    else if ( $mode == 'defaults' ) {
      $defaults = [
        'email_from' => Civi::settings()->get('email_from'),
        'email_archive' => Civi::settings()->get('email_archive'),
      ];
      return $defaults;
    }
    else if ( $mode == 'post' ) {
      $values = $this->exportValues();
      Civi::settings()->set('email_from', $values['email_from']);
      Civi::settings()->set('email_archive', $values['email_archive']);
    }
  }

  function postProcess() {
    parent::postProcess();
    $this->processOrgOptions('post');
    $this->processReceiptOptions('post');
    $this->processSystemOptions('post');
    $this->processEmailOptions('post');

    $statusMsg = ts('Your settings have been saved.', ['domain' => 'org.civicrm.cdntaxreceipts']);
    CRM_Core_Session::setStatus( $statusMsg, '', 'success' );
    // This is needed since otherwise it doesn't seem to show the recent changes.
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/cdntaxreceipts/settings', 'reset=1'));
  }
}
