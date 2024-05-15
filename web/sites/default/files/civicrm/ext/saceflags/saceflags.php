<?php

require_once 'saceflags.civix.php';
// phpcs:disable
use CRM_Saceflags_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function saceflags_civicrm_config(&$config): void {
  _saceflags_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function saceflags_civicrm_install(): void {
  _saceflags_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function saceflags_civicrm_postInstall(): void {
  _saceflags_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function saceflags_civicrm_uninstall(): void {
  _saceflags_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function saceflags_civicrm_enable(): void {
  _saceflags_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function saceflags_civicrm_disable(): void {
  _saceflags_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function saceflags_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _saceflags_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function saceflags_civicrm_entityTypes(&$entityTypes): void {
  _saceflags_civix_civicrm_entityTypes($entityTypes);
}

function saceflags_civicrm_buildForm($formName, &$form) {
  if ($formName == "CRM_Admin_Form_Options") {
    if ($form->getAction() == CRM_Core_Action::ADD || $form->getAction() == CRM_Core_Action::UPDATE) {
      //Get colour from AppointmentColours Entity
      $isFlags = \Civi\Api4\AppointmentColours::get()
        ->addSelect('colour_hex')
        ->addWhere('activity_type_id', '=', $form->getVar('_defaultValues')['value'])
        ->execute();
      //Pass colour to tpl
      $form->assign( 'activity_col_hex', $appointmentColourses[0]['colour_hex'] );
      //Add field to form
      $form->add('flag', 'saceflags', ts('Used for flagging contacts?'));
      CRM_Core_Region::instance('page-body')->add([
        'template' => 'saceflags.tpl',
      ]);
    }
  }
}

function saceflags_civicrm_postProcess($formName, &$form) {
  if ($formName == "CRM_Admin_Form_Options") {
    //Get form values
    $submitted = $form->getVar('_submitValues');
    $default_values = $form->getVar('_defaultValues');
    if(!$submitted['saceflags']){
      return;
    }

    $activityTypeID = $form->ajaxResponse['optionValue']['value'] ?: \Civi\Api4\OptionValue::get()->addSelect('value')->addWhere('id', '=',  $form->ajaxResponse['optionValue']['id'])->execute()->first()['value'];
    $id = \Civi\Api4\AppointmentColours::get(FALSE)
      ->addWhere('activity_type_id', '=', $activityTypeID)
      ->execute()->first()['id'];

    if (!$id) {
      \Civi\Api4\AppointmentColours::create()
        ->addValue('activity_type_id', $activityTypeID)
        ->addValue('is_flag', ltrim($submitted['activitycolor'], '#'))
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
//function saceflags_civicrm_preProcess($formName, &$form): void {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function saceflags_civicrm_navigationMenu(&$menu): void {
//  _saceflags_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _saceflags_civix_navigationMenu($menu);
//}
