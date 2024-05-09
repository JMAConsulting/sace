<?php
use CRM_SaceCivireports_ExtensionUtil as E;

class CRM_SaceCivireports_Form_Report_ActivitySystem extends CRM_Report_Form_Activity {

  public function __construct() {
    parent::__construct();
    $this->_columns['civicrm_activity']['fields']['activity_subject']['title'] = ts('Calendar Title');
    $this->_columns['civicrm_activity']['filters']['activity_subject']['title'] = ts('Calendar Title');
    $this->_columns['civicrm_contact']['fields']['contact_target']['title'] = ts('Organization');
    $this->_columns['civicrm_contact']['filters']['contact_target']['title'] = ts('Organization');
    $this->_columns['civicrm_contact']['fields']['contact_assignee']['title'] = ts('Staff');
    $this->_columns['civicrm_contact']['filters']['contact_assignee']['title'] = ts('Staff');
  }
}
