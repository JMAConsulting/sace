<?php

require_once 'activitywebform.civix.php';
// phpcs:disable
use CRM_Activitywebform_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function activitywebform_civicrm_config(&$config): void {
  _activitywebform_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function activitywebform_civicrm_install(): void {
  _activitywebform_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function activitywebform_civicrm_enable(): void {
  _activitywebform_civix_civicrm_enable();
}

function activitywebform_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_Options') {
    if ($form->getAction() == CRM_Core_Action::ADD || $form->getAction() == CRM_Core_Action::UPDATE) {
      $activityWebforms = \Civi\Api4\ActivityWebform::get()
        ->addSelect('webform_id')
        ->addWhere('activity_type_id', '=', $form->getVar('_defaultValues')['value'])
        ->execute();
      
      $activityWebformIds = [];
      foreach ($activityWebforms as $webform) {
        $activityWebformIds[] = $webform['webform_id'];
      }
      
      $webforms = \Drupal::entityTypeManager()->getStorage('webform')->loadMultiple(NULL);
      $options = [];
      foreach ($webforms as $webform_id => $webform) {
        $options[$webform_id] = $webform->get('title');
      }

      $form->add('select', 'activity_webform', ts('Activity Webform'),
        $options, FALSE, ['class' => 'crm-select2 huge', 'multiple' => 1]);

      if (!empty($activityWebformIds)) {
        $form->setDefaults(['activity_webform' => $activityWebformIds]);
      }

      CRM_Core_Region::instance('page-body')->add([
        'template' => 'activitywebform.tpl',
      ]);
    }
  }
}

function activitywebform_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_Options') {
    $submitted = $form->getVar('_submitValues');
    $activityTypeID = $form->ajaxResponse['optionValue']['value'] 
      ?: \Civi\Api4\OptionValue::get()
          ->addSelect('value')
          ->addWhere('id', '=', $form->ajaxResponse['optionValue']['id'])
          ->execute()
          ->first()['value'];

    $submittedActivityWebformIds = $submitted['activity_webform'];
    $existingActivityWebforms = \Civi\Api4\ActivityWebform::get(TRUE)
      ->addWhere('activity_type_id', '=', $activityTypeID)
      ->execute();

    $existingActivityWebformIds = [];
    foreach ($existingActivityWebforms as $activityWebform) {
      $existingActivityWebformIds[] = $activityWebform['webform_id'];
    }

    $newActivityWebformIds = [];
    $missingActivityWebformIds = $existingActivityWebformIds;
    if (!empty($submittedActivityWebformIds)) {
      $newActivityWebformIds = array_diff($submittedActivityWebformIds, $existingActivityWebformIds);
      $missingActivityWebformIds = array_diff($existingActivityWebformIds, $submittedActivityWebformIds);
    }

    foreach ($newActivityWebformIds as $newActivityWebformId) {
      \Civi\Api4\ActivityWebform::create(TRUE)
        ->addValue('activity_type_id', $activityTypeID)
        ->addValue('webform_id', $newActivityWebformId)
        ->execute();
    }
    foreach ($missingActivityWebformIds as $missingActivityWebformId) {
      \Civi\Api4\ActivityWebform::delete(TRUE)
        ->addWhere('activity_type_id', '=', $activityTypeID)
        ->addWhere('webform_id', '=', $missingActivityWebformId)
        ->execute();
    }
  }
}