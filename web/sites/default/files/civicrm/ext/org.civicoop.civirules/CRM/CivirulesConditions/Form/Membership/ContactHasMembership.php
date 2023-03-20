<?php
/**
 * Class for CiviRules Condition Contact has Membership Form
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */
class CRM_CivirulesConditions_Form_Membership_ContactHasMembership extends CRM_CivirulesConditions_Form_Form {

  /**
   * Method to get operators
   *
   * @return array
   * @access protected
   */
  protected function getOperators() {
    return CRM_CivirulesConditions_Membership_ContactHasMembership::getOperatorOptions();
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $membershipTypes = CRM_Civirules_Utils::getMembershipTypes();
    asort($membershipTypes);
    $membership_type_id = $this->add('select', 'membership_type_id', ts('Membership Type'), $membershipTypes, TRUE);
    $membership_type_id->setMultiple(TRUE);
    $this->add('select', 'type_operator', ts('Operator'), $this->getOperators(), TRUE);

    $membershipStatus = CRM_Civirules_Utils::getMembershipStatus(FALSE);
    asort($membershipStatus);
    $membership_status_id = $this->add('select', 'membership_status_id', ts('Membership Status'), $membershipStatus, TRUE);
    $membership_status_id->setMultiple(TRUE);
    $this->add('select', 'status_operator', ts('Operator'), $this->getOperators(), TRUE);

    $this->addDatePickerRange('start_date', ts('Membership Start Date'), FALSE, FALSE, 'From', 'To', NULL, '_to', '_from');
    $this->addDatePickerRange('join_date', ts('Membership Join Date'), FALSE, FALSE, 'From', 'To', NULL, '_to', '_from');
    $this->addDatePickerRange('end_date', ts('Membership End Date'), FALSE, FALSE, 'From', 'To', NULL, '_to', '_from');

    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE),
      array('type' => 'cancel', 'name' => ts('Cancel')),
    ));
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->ruleCondition->condition_params);
    if (!empty($data['membership_type_id'])) {
      $defaultValues['membership_type_id'] = $data['membership_type_id'];
    }
    if (!empty($data['type_operator'])) {
      $defaultValues['type_operator'] = $data['type_operator'];
    }
    if (!empty($data['membership_status_id'])) {
      $defaultValues['membership_status_id'] = $data['membership_status_id'];
    }
    if (!empty($data['status_operator'])) {
      $defaultValues['status_operator'] = $data['status_operator'];
    }
    $dateFields = ['start_date', 'join_date', 'end_date'];
    foreach ($dateFields as $dateField) {
      $defaultValues[$dateField . '_relative'] = $data[$dateField . '_relative'];
      $defaultValues[$dateField . '_to'] = $data[$dateField . '_to'];
      $defaultValues[$dateField . '_from'] = $data[$dateField . '_from'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   * @access public
   */
  public function postProcess() {
    $data['membership_type_id'] = $this->_submitValues['membership_type_id'];
    $data['type_operator'] = $this->_submitValues['type_operator'];
    $data['membership_status_id'] = $this->_submitValues['membership_status_id'];
    $data['status_operator'] = $this->_submitValues['status_operator'];
    $dateFields = ['start_date', 'join_date', 'end_date'];
    foreach ($dateFields as $dateField) {
      $data[$dateField . '_relative'] = $this->_submitValues[$dateField . '_relative'];
      $data[$dateField . '_to'] = empty($data[$dateField . '_relative']) ? $this->_submitValues[$dateField . '_to'] : NULL;
      $data[$dateField . '_from'] = empty($data[$dateField . '_relative']) ? $this->_submitValues[$dateField . '_from'] : NULL;
    }
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }

}
