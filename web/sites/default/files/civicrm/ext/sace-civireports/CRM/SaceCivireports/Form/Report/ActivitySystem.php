<?php
use CRM_SaceCivireports_ExtensionUtil as E;

class CRM_SaceCivireports_Form_Report_ActivitySystem extends CRM_Report_Form_Activity {

  public function __construct() {
    parent::__construct();
    $this->_columns['civicrm_activity']['fields']['id']['no_display'] = FALSE;
    $this->_columns['civicrm_activity']['fields']['id']['title'] = ts('Booking Reference ID');
    $this->_columns['civicrm_activity']['fields']['activity_subject']['title'] = ts('Calendar Title');
    $this->_columns['civicrm_activity']['fields']['details']['title'] = ts('Activity Notes');
    $this->_columns['civicrm_activity']['filters']['activity_subject']['title'] = ts('Calendar Title');
    $this->_columns['civicrm_contact']['fields']['contact_target']['title'] = ts('Organization');
    $this->_columns['civicrm_contact']['fields']['assignee_count'] = [
     'title' => ts('Number of participants'),
     'dbAlias' => '0',
    ];
    $this->_columns['civicrm_contact']['filters']['contact_target']['title'] = ts('Organization');
    $this->_columns['civicrm_contact']['fields']['contact_assignee']['title'] = ts('Staff');
    $this->_columns['civicrm_contact']['filters']['contact_assignee']['title'] = ts('Staff');
    $requiredFields = [
      'civicrm_activity' => ['activity_date_time'],
      'civicrm_value_booking_infor_2' => ['custom_334', 'custom_331', 'custom_330', 'custom_126', 'custom_125', 'custom_124', 'custom_123', 'custom_122', 'custom_121', 'custom_119', 'custom_90', 'custom_43', 'custom_25', 'custom_34', 'custom_35', 'custom_127', 'custom_128', 'custom_658', 'custom_662', 'custom_1233', 'custom_815', 'custom_1261',],
      'civicrm_value_sace_staff_fi_14' => 'all',
    ];
    foreach ($requiredFields as $tableName => $fields) {
      if ($fields == 'all') {
        foreach($this->_columns[$tableName]['fields'] as $fieldName => $value) {
          $this->_columns[$tableName]['fields'][$fieldName]['required'] = TRUE;
        }
      }
else {
      foreach ($fields as $fieldName) {
        $this->_columns[$tableName]['fields'][$fieldName]['required'] = TRUE;
      }
}
    }
  }

public function statistics(&$rows) {
  $statistics = parent::statistics($rows);
  $sql = "SELECT count(distinct contact_id) FROM civicrm_activity_contact WHERE activity_id IN (SELECT DISTINCT activity_civireport.id {$this->_from} {$this->_where}) AND record_type_id = 1";
  $this->addToDeveloperTab("Query for fetching total participant count: \n\n" . $sql);
  $statistics['counts']['participant_count'] = [
    'value' => CRM_Core_DAO::singleValueQuery($sql),
    'title' => ts('Total Participants'),
    'type' => CRM_Utils_Type::T_INT,
  ];
  return $statistics;
}

  public function alterDisplay(&$rows) {
    foreach ($rows as $id => $row) {
      if (!empty($row['civicrm_contact_contact_assignee'])) {
         $rows[$id]['civicrm_contact_assignee_count'] = count(explode(';', $row['civicrm_contact_contact_assignee']));
      }
    }
    parent::alterDisplay($rows);

  }
}
