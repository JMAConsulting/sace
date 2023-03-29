<?php
use CRM_SaceCivireports_ExtensionUtil as E;

class CRM_SaceCivireports_Form_Report_ExtendingContributionSummary extends CRM_Report_Form_Contribute_Summary {

  public function __construct() {
    parent::__construct();
    $this->_columns['civicrm_contact']['group_bys']['contact_type'] = ['title' => ts('Contact Type')];
    $this->_columns['civicrm_contribution']['fields']['total_donors'] = [
      'title' => E::ts('# of donors'),
      'dbAlias' => 'COUNT(DISTINCT contribution_civireport.contact_id)',
      'type' => CRM_Utils_Type::T_STRING,
    ];
  }

}
