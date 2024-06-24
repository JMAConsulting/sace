<?php
use CRM_SaceCivireports_ExtensionUtil as E;

class CRM_SaceCivireports_Form_Report_ExtendingContributionDetail extends CRM_Report_Form_Contribute_Detail {

  public function __construct() {
    parent::__construct();
    CRM_Core_DAO::disableFullGroupByMode();
    $this->_columns['civicrm_contact']['group_bys'] = [
      'id' => [
        'name' => 'id',
        'required' => TRUE,
        'title' => ts('Contact'),
      ],
    ];

    $this->_columns['civicrm_contact']['fields']['organization_name'] = [
      'title' => E::ts('Org Name'),
      'no_repeat' => TRUE,
    ];
    $this->_columns['civicrm_contact']['fields']['display_name']['title'] = E::ts('Display Name');
    $this->_columns['civicrm_address']['fields']['address_supplemental_address_1']['title'] = E::ts('Address 2');
    $this->_columns['civicrm_contribution']['fields']['recent_donation_date'] = [
      'title' => E::ts('Most recent donation date'),
      'dbAlias' => '(MAX(contribution_civireport.receive_date))',
      'type' => CRM_Utils_Type::T_STRING,
    ];
    $this->_columns['civicrm_contribution']['fields']['first_date'] = [
      'title' => E::ts('Date of first donation'),
      'dbAlias' => '(SELECT MIN(receive_date) FROM civicrm_contribution WHERE contact_id = contribution_civireport.contact_id)',
      'type' => CRM_Utils_Type::T_STRING,
    ];
    $this->_columns['civicrm_contribution']['fields']['recent_donation_amount'] = [
      'title' => E::ts('Most recent donation amount'),
     // 'dbAlias' => 'contribution_civireport.total_amount',
      'dbAlias' => '(SELECT total_amount FROM civicrm_contribution WHERE contact_id = contribution_civireport.contact_id ORDER BY id desc LIMIT 1)',
      'type' => $this->_columns['civicrm_contribution']['fields']['total_amount']['type'],
    ];
    $this->_columns['civicrm_contribution']['fields']['largest_donation_amount'] = [
      'title' => E::ts('Largest contribution'),
      'dbAlias' => '(MAX(contribution_civireport.total_amount))',
      'type' => $this->_columns['civicrm_contribution']['fields']['total_amount']['type'],
    ];
/*
    $this->_columns['civicrm_contribution']['fields']['median'] = [
      'title' => E::ts('Median'),
      'dbAlias' => '(1)',
      'type' => $this->_columns['civicrm_contribution']['fields']['total_amount']['type'],
    ];
*/
    $this->_columns['civicrm_contribution']['fields']['total_count'] = [
      'title' => E::ts('# of donations made for the year ' . date('Y')),
      'dbAlias' => '(SELECT COUNT(DISTINCT id) FROM civicrm_contribution WHERE YEAR(receive_date) = YEAR(CURRENT_DATE) AND contact_id = contribution_civireport.contact_id)',
      'type' => CRM_Utils_Type::T_STRING,
    ];

    $this->_columns['civicrm_contribution']['fields']['total_lifetime_amount'] = [
      'title' => E::ts('Total lifetime donations'),
      'dbAlias' => '(SELECT SUM(total_amount) FROM civicrm_contribution WHERE contact_id = contribution_civireport.contact_id)',
      'type' => CRM_Utils_Type::T_STRING,
    ];

    $this->_columns['civicrm_contribution']['fields']['avg_amount'] = [
      'title' => E::ts('Average donation amount'),
      'dbAlias' => '(SELECT AVG(total_amount) FROM civicrm_contribution WHERE contact_id = contribution_civireport.contact_id)',
      'type' => $this->_columns['civicrm_contribution']['fields']['total_amount']['type'],
    ];

    $this->_columns['civicrm_contribution']['fields']['recurring_ornot'] = [
      'title' => E::ts('One-time or recurring/monthly'),
      'dbAlias' => '(SELECT SUM(contribution_recur_id) FROM civicrm_contribution WHERE contact_id = contribution_civireport.contact_id)',
      'type' => CRM_Utils_Type::T_STRING,
    ];
    $this->_columns['civicrm_contribution']['fields']['total_amount']['no_display'] = TRUE;
    $this->_columns['civicrm_contribution']['group_bys']['contribution_id']['required'] = FALSE;
    $this->_columns['civicrm_contribution']['group_bys']['currency'] = [
        'name' => 'currency',
        'required' => TRUE,
        'title' => ts('Currency'),
    ];
    $this->_columns['civicrm_contribution']['group_bys']['financial_type_id'] = [
        'title' => ts('Allocation type'),
    ];
    unset($this->_columns['civicrm_contribution']['group_bys']['contribution_id']);
  }

  protected function getSelectClauseWithGroupConcatIfNotGroupedBy($tableName, &$fieldName, &$field) {
    CRM_Core_DAO::disableFullGroupByMode();
    if ($this->groupConcatTested && (!empty($this->_groupByArray) || $this->isForceGroupBy)) {
      if ((empty($field['statistics']) || in_array('GROUP_CONCAT', $field['statistics']))) {
        $label = $field['title'] ?? NULL;
        $alias = $field['tplField'] ?? "{$tableName}_{$fieldName}";
        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $label;
        $this->_selectAliases[] = $alias;
        if (empty($this->_groupByArray[$tableName . '_' . $fieldName]) && !in_array($field['name'], ['recent_donation_date', 'recent_donation_amount', 'total_count', 'total_lifetime_amount', 'avg_amount', 'recurring_ornot'])) {
       //   return "GROUP_CONCAT(DISTINCT {$field['dbAlias']}) as $alias";
        }
        return "({$field['dbAlias']}) as $alias";
      }
    }
  }

  public function statistics(&$rows) {
    return [];
  }

  public function alterDisplay(&$rows) {
    parent::alterDisplay($rows);
    foreach ($rows as $rowNum => $row) {
      if ($value = CRM_Utils_Array::value('civicrm_contribution_avg_amount', $row)) {
        $rows[$rowNum]['civicrm_contribution_avg_amount'] = CRM_Utils_Money::format($value, $rows[$rowNum]['civicrm_contribution_currency']);
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_total_lifetime_amount', $row)) {
        $rows[$rowNum]['civicrm_contribution_total_lifetime_amount'] = CRM_Utils_Money::format($value, $rows[$rowNum]['civicrm_contribution_currency']);
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_recent_donation_amount', $row)) {
        $rows[$rowNum]['civicrm_contribution_recent_donation_amount'] = CRM_Utils_Money::format($value, $rows[$rowNum]['civicrm_contribution_currency']);
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_largest_donation_amount', $row)) {
        $rows[$rowNum]['civicrm_contribution_largest_donation_amount'] = CRM_Utils_Money::format($value, $rows[$rowNum]['civicrm_contribution_currency']);
      }
      $rows[$rowNum]['civicrm_contribution_recurring_ornot'] = (CRM_Utils_Array::value('civicrm_contribution_recurring_ornot', $row, 0) > 0) ? 'Recurring' : 'One-time';
    }

  }

public function beginPostProcessCommon() {
    CRM_Core_DAO::disableFullGroupByMode();
parent::beginPostProcessCommon();
}

}
