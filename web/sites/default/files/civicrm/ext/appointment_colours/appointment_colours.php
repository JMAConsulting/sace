<?php

require_once 'appointment_colours.civix.php';
// phpcs:disable
use CRM_AppointmentColours_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function appointment_colours_civicrm_config(&$config) {
  _appointment_colours_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function appointment_colours_civicrm_install(): void {
  _appointment_colours_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function appointment_colours_civicrm_postInstall(): void {
  _appointment_colours_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function appointment_colours_civicrm_uninstall(): void {
  _appointment_colours_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function appointment_colours_civicrm_enable(): void {
  _appointment_colours_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function appointment_colours_civicrm_disable(): void {
  _appointment_colours_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function appointment_colours_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _appointment_colours_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function appointment_colours_civicrm_entityTypes(&$entityTypes): void {
  _appointment_colours_civix_civicrm_entityTypes($entityTypes);
}

function appointment_colours_civicrm_buildForm($formName, &$form) {
  if ($formName == "CRM_Admin_Form_Options") {
    if ($form->getAction() == CRM_Core_Action::ADD || $form->getAction() == CRM_Core_Action::UPDATE) {
      //Get colour from AppointmentColours Entity
      $appointmentColourses = \Civi\Api4\AppointmentColours::get()
        ->addSelect('colour_hex')
        ->addWhere('activity_type_id', '=', $form->getVar('_defaultValues')['value'])
        ->execute();
      //Pass colour to tpl
      $form->assign( 'activity_col_hex', $appointmentColourses[0]['colour_hex'] );
      //Add field to form
      $form->add('color', 'activitycolor', ts('Activity Colour'));
      CRM_Core_Region::instance('page-body')->add([
        'template' => 'activitycolor.tpl',
      ]);
    }
  }
}

function appointment_colours_civicrm_postProcess($formName, &$form) {
  if ($formName == "CRM_Admin_Form_Options") {
    //Get form values
    $submitted = $form->getVar('_submitValues');
    $default_values = $form->getVar('_defaultValues');
    if(!$submitted['activitycolor']){
      return;
    }

    $activityTypeID = $form->ajaxResponse['optionValue']['value'] ?: \Civi\Api4\OptionValue::get()->addSelect('value')->addWhere('id', '=',  $form->ajaxResponse['optionValue']['id'])->execute()->first()['value'];
    $id = \Civi\Api4\AppointmentColours::get(FALSE)
      ->addWhere('activity_type_id', '=', $activityTypeID)
      ->execute()->first()['id'];

    if (!$id) {
      \Civi\Api4\AppointmentColours::create()
        ->addValue('activity_type_id', $activityTypeID)
        ->addValue('colour_hex', ltrim($submitted['activitycolor'], '#'))
        ->execute();
    }
    else {
      \Civi\Api4\AppointmentColours::update(FALSE)
        ->addWhere('id', '=', $id)
        ->addValue('colour_hex', ltrim($submitted['activitycolor'], '#'))
        ->execute();
    }
  }
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function appointment_colours_civicrm_preProcess($formName, &$form): void {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function appointment_colours_civicrm_navigationMenu(&$menu): void {
//  _appointment_colours_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _appointment_colours_civix_navigationMenu($menu);
//}
