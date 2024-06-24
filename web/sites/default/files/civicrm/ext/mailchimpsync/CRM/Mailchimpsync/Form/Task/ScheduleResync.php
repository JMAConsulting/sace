<?php

use CRM_Mailchimpsync_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Mailchimpsync_Form_Task_ScheduleResync extends CRM_Contact_Form_Task {
  public function buildQuickForm() {

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Schedule Re-sync'),
        'isDefault' => TRUE,
      ),
    ));
    // export form elements
    // $this->assign('elementNames', $this->getRenderableElementNames());
    $this->assign('contactsCount', count($this->_contactIds));
    parent::buildQuickForm();
  }

  public function postProcess() {
    $n = count($this->_contactIds);
    if ($n) {
      // CRM_Core_Session::setStatus(E::ts('You picked color "%1"', array( 1 => $options[$values['favorite_color']],)));
      $result = civicrm_api3('Mailchimpsync', 'includecontact', ['contact_id' => $this->_contactIds]);
      Civi::log()->debug("includecontact result: ", ['result' => $result]);
      CRM_Core_Session::setStatus(E::ts('%1 contact(s) were scheduled for the next Mailchimp Sync',
        [1 => $n]));
    }
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
