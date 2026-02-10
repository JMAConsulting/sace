<?php
use CRM_SaceCivireports_ExtensionUtil as E;

class CRM_SaceCivireports_Form_Report_WiseguyzSummaryReport extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE;

  public $_mapper = NULL;

  protected $_customGroupExtends = 'Activity';

  public $_demographicFields = [];
  public $__audienceFields = [];
  public function __construct() {
    $this->_columns = [
      'civicrm_activity' => [
        'dao' => 'CRM_Activity_DAO_Activity',
        'fields' => [
          'id' => [
            'title' => ts('Booking Reference ID'),
            'required' => TRUE,
          ],
          'activity_type_id' => [
           'title' => E::ts('Booking Type'),
           'required' => TRUE,
          ],
          'activity_date_time' => [
            'title' => E::ts('Start Date'),
            'required' => TRUE,
          ],
          'subject' => [
            'title' => E::ts('Calendar Title'),
          ],
          'duration' => [
            'title' => ts('Length (in minutes)'),
            'required' => TRUE,
          ],
          'details' => [
            'title' => E::ts('Staff Notes'),
          ],
        ],
        'filters' => [
          'activity_type_id' => [
            'title' => ts('Booking Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_OptionGroup::values('activity_type'),
          ],
          'activity_subject' => ['title' => ts('Calendar Title')],
          'activity_date_time' => [
            'type' => CRM_Utils_Type::T_DATE,
            'title' => E::ts('Start date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
          ],
          'duration' => [
            'title' => ts('Length (in minutes)'),
            'type' => CRM_Utils_Type::T_INT,
          ],
          'contact_id' => [
            'name' => 'contact_id',
            'title' => ts('Presenter(s)'),
            'dbAlias' => 'sc.id',
            'operatorType' => CRM_Report_Form::OP_ENTITYREF,
            'type' => CRM_Utils_Type::T_INT,
            'attributes' => [
              'entity' => 'Contact',
              'select' => ['minimumInputLength' => 0],
            ],
          ],
        ],
      ],
      'civicrm_contact_sc' => [
        'dao'     => 'CRM_Contact_DAO_Contact',
        'fields' => [
          'presenter_1'   => [
            'title'     => ts('Presenter 1'),
            'alias'     => 'sc',
            'dbAlias' => "GROUP_CONCAT(DISTINCT CONCAT(sc.id, '-', sc.display_name))",
          ],
          'presenter_2'   => [
            'title'     => ts('Presenter 2'),
            'alias'     => 'sc',
            'dbAlias' => "1",
          ],
          'presenter_3'   => [
            'title'     => ts('Presenter 3'),
            'alias'     => 'sc',
            'dbAlias' => "1",
          ],
          'presenter_4'   => [
            'title'     => ts('Presenter 4'),
            'alias'     => 'sc',
            'dbAlias' => '1',
          ],
        ],
      ],
      'civicrm_contact_tc' => [
        'dao'     => 'CRM_Contact_DAO_Contact',
        'fields' => [
          'display_name'   => [
            'title'     => ts('School/Organization'),
            'alias'     => 'tc',
            'required' => TRUE,
            'dbAlias' => "CONCAT(tc.id, '-', tc.display_name)",
          ],
          'postal_code' => [
            'title'     => ts('Postal Code'),
            'dbAlias' => "tc_add.postal_code",
          ],
          'tag_id' => [
            'title' => ts('Tags (for Organization/School)'),
            'dbAlias' => 'GROUP_CONCAT(DISTINCT tc_tag.name SEPARATOR \', \')',
          ],
        ],
        'filters' => [
          'tag' => [
            'title' => ts('Tags (for Organization/School)'),
            'dbAlias' => 'tc_tag.id',
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_BAO_Tag::getTags('civicrm_contact'),
          ],
        ],
      ],
    ];

    parent::__construct();
$tables = CRM_Utils_Array::collect('table_name', CRM_Core_DAO::executeQuery("
SELECT table_name 
from civicrm_custom_group 
where (name IN ('Feedback_Summary', 'Booking_Information', 'Feedback_Form')) AND extends = 'Activity'
")->fetchAll());

    $keys = ['civicrm_value_ped_booking_r_53', 'civicrm_value_ped_presentat_54', 'civicrm_value_clin_activiti_9', 'civicrm_value_mailchimp_cam_64', '', 'civicrm_value_ped_participa_49'];
   
    foreach (array_keys($this->_columns) as $key) {
      if (!in_array($key, $tables) && strstr($key, 'civicrm_value_')) {unset($this->_columns[$key]);}
    }

 }

public function addCustomDataToColumns($addFields = TRUE, $permCustomGroupIds = []) {
  parent::addCustomDataToColumns($addFields, $permCustomGroupIds);
}

  public function preProcess() {
    $this->assign('reportTitle', E::ts('Public Ed Bookings Report'));
    parent::preProcess();
  }

  public function from() {
    $this->_from = "
      FROM civicrm_activity {$this->_aliases['civicrm_activity']}
      INNER JOIN (
        SELECT a.id, entity_id, booking_1442
        FROM civicrm_value_feedback_form_61 bf
         INNER JOIN civicrm_activity a ON a.id = bf.entity_id AND a.activity_type_id IN (341)
      ) summary ON summary.booking_1442 = {$this->_aliases['civicrm_activity']}.id
      INNER JOIN civicrm_value_feedback_form_61 {$this->_aliases['civicrm_value_feedback_form_61']} ON {$this->_aliases['civicrm_value_feedback_form_61']}.entity_id = summary.entity_id
      LEFT JOIN civicrm_value_booking_infor_2 {$this->_aliases['civicrm_value_booking_infor_2']} ON {$this->_aliases['civicrm_value_booking_infor_2']}.entity_id = {$this->_aliases['civicrm_value_feedback_form_61']}.booking_1442
      LEFT JOIN civicrm_activity_contact assignee ON assignee.activity_id = {$this->_aliases['civicrm_value_feedback_form_61']}.booking_1442 AND assignee.record_type_id = 2
      LEFT JOIN civicrm_activity_contact target ON target.activity_id = {$this->_aliases['civicrm_value_feedback_form_61']}.booking_1442 AND target.record_type_id = 3
      LEFT JOIN civicrm_activity_contact source ON source.activity_id = {$this->_aliases['civicrm_value_feedback_form_61']}.booking_1442 AND source.record_type_id = 1
      LEFT JOIN civicrm_contact ac ON ac.id = assignee.contact_id
      LEFT JOIN civicrm_contact tc ON tc.id = target.contact_id
      LEFT JOIN civicrm_address tc_add ON tc_add.contact_id = tc.id AND tc_add.is_primary = 1
      LEFT JOIN civicrm_entity_tag tc_entity_tag ON tc_entity_tag.entity_id = tc.id AND tc_entity_tag.entity_table = 'civicrm_contact'
      LEFT JOIN civicrm_tag tc_tag ON tc_tag.id = tc_entity_tag.tag_id
      LEFT JOIN civicrm_contact sc ON sc.id = source.contact_id
   ";

   $tables = CRM_Core_DAO::executeQuery("
SELECT table_name 
from civicrm_custom_group 
where (name = 'Feedback_Summary') AND extends = 'Activity'
")->fetchAll();

foreach ($tables as $table) {
  $this->_from .= sprintf("LEFT JOIN %s %s ON summary.id = %s.entity_id
  ", $table['table_name'], $this->_aliases[$table['table_name']], $this->_aliases[$table['table_name']]);
}

  }

  public function customDataFrom($joinsForFiltersOnly = FALSE) {}

  public function alterCustomDataDisplay(&$rows) {
    $customFields = [];
    $customFieldIds = [];
    foreach ($this->_params['fields'] as $fieldAlias => $value) {
      if ($fieldId = CRM_Core_BAO_CustomField::getKeyID($fieldAlias)) {
        $customFieldIds[$fieldAlias] = $fieldId;
      }
    }

    if (empty($customFieldIds)) {
      return;
    }

    // skip for type date and ContactReference since date format is already handled
    $query = "
SELECT cg.table_name, cf.id
FROM  civicrm_custom_field cf
INNER JOIN civicrm_custom_group cg ON cg.id = cf.custom_group_id
WHERE cg.is_active = 1 AND
      cf.is_active = 1 AND
      cf.data_type   NOT IN ('ContactReference', 'Date') AND
      cf.id IN (" . implode(",", $customFieldIds) . ")";

    $dao = CRM_Core_DAO::executeQuery($query);
    while ($dao->fetch()) {
      $customFields[$dao->table_name . '_custom_' . $dao->id] = $dao->id;
    }

    $entryFound = FALSE;
    foreach ($rows as $rowNum => $row) {
      foreach ($row as $tableCol => $val) {
        if (array_key_exists($tableCol, $customFields)) {
          $rows[$rowNum][$tableCol] = CRM_Core_BAO_CustomField::displayValue($val, $customFields[$tableCol]);
          $entryFound = TRUE;
        }
      }

      // skip looking further in rows, if first row itself doesn't
      // have the column we need
      if (!$entryFound) {
        break;
      }
    }
  }

  public function groupBy() {
    $this->_groupBy = "
      GROUP BY {$this->_aliases['civicrm_activity']}.id";
  }

  public function alterDisplay(&$rows) {
    $activityType = CRM_Core_PseudoConstant::activityType(TRUE, TRUE, FALSE, 'label', TRUE);
    $selectedAids = CRM_Utils_Array::collect('civicrm_activity_id', $rows);
    foreach ($rows as $rowNum => &$row) {
      if (!empty($row['civicrm_value_ped_participa_49_age'])) {
        $records = (array) explode(',', $row['civicrm_value_ped_participa_49_age']);
        $values = array_reduce($records, fn($carry, $record) => ($key = explode('-', $record)[1]) && ($carry[$key] = ($carry[$key] ?? 0) + 1) ? $carry : $carry, []);
        $row['civicrm_value_ped_participa_49_age'] = implode(', ', array_map(fn($k, $v) => "$k ($v)", array_keys($values), $values));
      }
      if (array_key_exists('civicrm_activity_activity_type_id', $row)) {
        if ($value = $row['civicrm_activity_activity_type_id']) {
          $rows[$rowNum]['civicrm_activity_activity_type_id'] = $activityType[$value];
        }
     }
     if (!empty($row['civicrm_contact_sc_presenter_1'])) {
        $contacts = explode(',', $row['civicrm_contact_sc_presenter_1']);
        $count = 1;
        foreach ($contacts as $contact) {
          $matches = explode('-', $contact);
          $row['civicrm_contact_sc_presenter_'. $count] = sprintf('<a href="%s" target="_blank">%s</a>', CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $matches[0]), $matches[1]);
          $count++;
        }
        while($count <= 4) {
          $row['civicrm_contact_sc_presenter_' . $count] = '';
          $count++;
        }
      }
      else {
        $row['civicrm_contact_sc_presenter_2'] = $row['civicrm_contact_sc_presenter_3'] = $row['civicrm_contact_sc_presenter_4'] = '';
      }
      if (!empty($row['civicrm_contact_tc_display_name'])) {
        $matches = explode('-', $row['civicrm_contact_tc_display_name']);
        $row['civicrm_contact_tc_display_name'] = sprintf('<a href="%s" target="_blank">%s</a>', CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $matches[0]), $matches[1]);
      }
    }
  }
}
