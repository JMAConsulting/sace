<?php

require_once 'sesconfigurationsets.civix.php';
// phpcs:disable
use CRM_Sesconfigurationsets_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function sesconfigurationsets_civicrm_config(&$config): void {
  _sesconfigurationsets_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function sesconfigurationsets_civicrm_install(): void {
  _sesconfigurationsets_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function sesconfigurationsets_civicrm_enable(): void {
  // set the default configuration set value
  // but only if we haven't set it before
  if (!Civi::settings()->get('sesconfigurationsets')) {
    // get rid of http:// or https:// before the url and remove any trailing slashes
    $url = preg_replace("/https?:\/\//", '', rtrim(CIVICRM_UF_BASEURL, '/'));
    $default = str_replace('.', '-', $url) . '-ses-cs';
    Civi::settings()->set('sesconfigurationsets', $default);
  }
  _sesconfigurationsets_civix_civicrm_enable();
}

function sesconfigurationsets_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_Preferences_Mailing') {
    $configurationSet = Civi::settings()->get('sesconfigurationsets');
    $form->add('text', 'configuration_set', ts('Configuration set'));
    if ($configurationSet) {
      $form->setDefaults(['configuration_set' => $configurationSet]);
    }
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'configurationset.tpl',
    ));
  }
}

function sesconfigurationsets_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_Preferences_Mailing') {
    $submitted = $form->getVar('_submitValues');
    $submittedConfigurationSet = $submitted['configuration_set'];
    Civi::settings()->set('sesconfigurationsets', $submittedConfigurationSet);
  }
}

function sesconfigurationsets_civicrm_alterMailParams(&$params, $context) {
  $configurationSet = Civi::settings()->get('sesconfigurationsets');
  $params['headers']['X-SES-CONFIGURATION-SET'] = $configurationSet;
}
