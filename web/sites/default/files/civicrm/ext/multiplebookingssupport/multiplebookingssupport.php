<?php

require_once 'multiplebookingssupport.civix.php';
// phpcs:disable
use CRM_Multiplebookingssupport_ExtensionUtil as E;
// phpcs:enable
use Civi\Api4\MultipleBooking;
use Civi\Api4\OptionValue;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function multiplebookingssupport_civicrm_config(&$config): void {
  _multiplebookingssupport_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function multiplebookingssupport_civicrm_install(): void {
  _multiplebookingssupport_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function multiplebookingssupport_civicrm_enable(): void {
  _multiplebookingssupport_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
function multiplebookingssupport_civicrm_buildForm($formName, &$form): void {
  if ($formName === 'CRM_Admin_Form_Options' && $form->getVar('_gName') === 'activity_type') {
    $form->addField('is_multiple_booking', [
      'entity' => 'multiple_booking',
      'action' => 'create',
    ]);
    if (!empty($form->_id)) {
      $multipleBooking = MultipleBooking::get(FALSE)
        ->addJoin('OptionValue AS option_value', 'INNER', ['option_value.value', '=', 'activity_type_id'])
        ->addWhere('option_value.id', '=', $form->_id)
        ->execute();
      if (count($multipleBooking) > 0) {
        $form->setDefaults(['is_multiple_booking' => $multipleBooking[0]['is_multiple_booking']]);
      }
    }
    CRM_Core_Region::instance('form-bottom')->add([
      'template' => 'CRM/Admin/Form/ActivityTypeMultipleBooking.tpl',
    ]);
  }
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
function multiplebookingssupport_civicrm_postProcess($formName, &$form): void {
  if ($formName === 'CRM_Admin_Form_Options' && $form->getVar('_gName') === 'activity_type') {
    $multipleBookingValue = $form->getSubmittedValue('is_multiple_booking') ?? 0;
    $optionValue = OptionValue::get(FALSE)->addWhere('id', '=', $form->_id)->execute()->first();
    MultipleBooking::save(FALSE)->setRecords([['is_multiple_booking' => $multipleBookingValue, 'activity_type_id' => $optionValue['value']]])->setMatch(['activity_type_id'])->execute();
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function multiplebookingssupport_civicrm_navigationMenu(&$menu): void {
//  _multiplebookingssupport_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _multiplebookingssupport_civix_navigationMenu($menu);
//}
