<?php

use CRM_Cdntaxreceipts_ExtensionUtil as E;

// We don't use any of this metadata but in 6.19+ it complains when we try to
// set the setting if it's not here.

$defaults = [
  'is_domain' => 1,
  'is_contact' => 0,
  'help_text' => '',
  'description' => '',
  'type' => 'String',
  'quick_form_type' => 'Element',
  'html_type' => 'text',
  'html_attributes' => [
    'size' => 256,
    'maxlength' => 256,
  ],
  'default' => '',
];
$settings = [
  'cdntaxreceipts_source_field' => array_merge($defaults, [
    'name' => 'cdntaxreceipts_source_field',
    // what does title do for any of these?
    'title' => E::ts('Source Field Value'),
    // 'default' => '{contribution.source}',
    // But we can't default to the above because if you have a non-blank string default in a settings file then you can never actually blank out the value, and we want to allow that, so see postInstall instead.
    'default' => '',
  ]),
  'org_name' => array_merge($defaults, [
    'name' => 'org_name',
    'title' => E::ts('Organization Name'),
  ]),
  'org_address_line1' => array_merge($defaults, [
    'name' => 'org_address_line1',
    'title' => E::ts('Address Line 1'),
  ]),
  'org_address_line2' => array_merge($defaults, [
    'name' => 'org_address_line2',
    'title' => E::ts('Address Line 2'),
  ]),
  'org_tel' => array_merge($defaults, [
    'name' => 'org_tel',
    'title' => E::ts('Telephone'),
  ]),
  'org_fax' => array_merge($defaults, [
    'name' => 'org_fax',
    'title' => E::ts('Fax'),
  ]),
  'org_email' => array_merge($defaults, [
    'name' => 'org_email',
    'title' => E::ts('Email'),
  ]),
  'org_web' => array_merge($defaults, [
    'name' => 'org_web',
    'title' => E::ts('Website'),
  ]),
  'org_charitable_no' => array_merge($defaults, [
    'name' => 'org_charitable_no',
    'title' => E::ts('Charitable Registration Number'),
  ]),
  'receipt_prefix' => array_merge($defaults, [
    'name' => 'receipt_prefix',
    'title' => E::ts('Receipt Prefix'),
  ]),
  'receipt_serial' => array_merge($defaults, [
    'name' => 'receipt_serial',
    'title' => E::ts('Receipt Serial'),
  ]),
  'receipt_authorized_signature_text' => array_merge($defaults, [
    'name' => 'receipt_authorized_signature_text',
    'title' => E::ts('Authorized Signature Text'),
  ]),
  'receipt_location_issued' => array_merge($defaults, [
    'name' => 'receipt_location_issued',
    'title' => E::ts('Location Issued'),
  ]),
  'email_from' => array_merge($defaults, [
    'name' => 'email_from',
    'title' => E::ts('Email From'),
  ]),
  'email_archive' => array_merge($defaults, [
    'name' => 'email_archive',
    'title' => E::ts('Email Archive'),
  ]),
  'issue_inkind' => array_merge($defaults, [
    'name' => 'issue_inkind',
    'title' => E::ts('Setup in-kind receipts?'),
    'type' => 'Boolean',
    'html_type' => 'checkbox',
    'html_attributes' => [],
    'default' => 0,
  ]),
  'delivery_method' => array_merge($defaults, [
    'name' => 'delivery_method',
    'title' => E::ts('Delivery Method'),
    'html_type' => 'radio',
    'html_attributes' => [],
    'options' => [
      CDNTAX_DELIVERY_PRINT_ONLY => E::ts('Print only'),
      CDNTAX_DELIVERY_PRINT_EMAIL => E::ts('Email or print'),
      CDNTAX_DELIVERY_DATA_ONLY => E::ts('Data only'),
    ],
    'default' => CDNTAX_DELIVERY_PRINT_ONLY,
  ]),
  'attach_to_workflows' => array_merge($defaults, [
    'name' => 'attach_to_workflows',
    'title' => E::ts('Attach receipts to automated workflow messages?'),
    'html_type' => 'radio',
    'html_attributes' => [],
    'options' => [
      1 => E::ts('Yes'),
      0 => E::ts('No'),
    ],
    'default' => 0,
  ]),
  'enable_advanced_eligibility_report' => array_merge($defaults, [
    'name' => 'enable_advanced_eligibility_report',
    'title' => E::ts('Enable Advanced Eligibility Check?'),
    'html_type' => 'radio',
    'html_attributes' => [],
    'options' => [
      1 => E::ts('Yes'),
      0 => E::ts('No'),
    ],
    'default' => 0,
  ]),
  'receipt_logo' => array_merge($defaults, [
    'name' => 'receipt_logo',
    'title' => E::ts('Organization Logo'),
  ]),
  'receipt_signature' => array_merge($defaults, [
    'name' => 'receipt_signature',
    'title' => E::ts('Signature Image'),
  ]),
  'receipt_watermark' => array_merge($defaults, [
    'name' => 'receipt_watermark',
    'title' => E::ts('Watermark Image'),
  ]),
  'receipt_pdftemplate' => array_merge($defaults, [
    'name' => 'receipt_pdftemplate',
    'title' => E::ts('PDF Template'),
  ]),
  'cdntaxreceipts_inkind' => array_merge($defaults, [
    'name' => 'cdntaxreceipts_inkind',
    'title' => E::ts('In-kind Financial Type'),
    'type' => 'Integer',
    'default' => 0,
  ]),
  'cdntaxreceipts_mocktime' => array_merge($defaults, [
    'name' => 'cdntaxreceipts_mocktime',
    'title' => 'dontcare',
    'type' => 'Integer',
    'default' => NULL,
  ]),
];
// I'm really not sure what we're supposed to do with these but this works.
// And this is a bit silly because I'm not sure how to translate the
// default value at the time this runs.
$locales = CRM_Core_I18n::getMultilingual();
if (!$locales) {
  $locales = ['en_US', 'en_CA', 'fr_CA'];
}
foreach ($locales as $locale) {
  $settings['cdntaxreceipts_source_label_' . $locale] = array_merge($defaults, [
    'name' => 'cdntaxreceipts_source_label_' . $locale,
    'title' => E::ts('Source Field Label (%1)', [1 => $locale]),
    // The space in "Source: " is not a typo.
    // 'default' => E::ts('Source: '),
    // But we can't default to the above because if you have a non-blank string default in a settings file then you can never actually blank out the value, and we want to allow that, so see postInstall instead.
    'default' => '',
  ]);
}

return $settings;
