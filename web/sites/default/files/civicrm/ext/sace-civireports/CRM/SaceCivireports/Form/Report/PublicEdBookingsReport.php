<?php
use CRM_SaceCivireports_ExtensionUtil as E;

class CRM_SaceCivireports_Form_Report_PublicEdBookingsReport extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE;

  public $_mapper = NULL;

  /**
   * protected $_customGroupExtends = ['Activity'];
   */
  public function __construct() {
    $this->_columns = [
      'civicrm_value_ped_booking_r_53' => [
        'dao' => 'CRM_Activity_DAO_Activity',
        'fields' => [
          'custom_706' => [
            'title' => ts('Booking Ref'),
            'required' => TRUE,
            'no_display' => TRUE,
            'dbAlias' => 'booking_reference_id_706',
          ],
        ],
      ],
      'civicrm_value_booking_infor_2' => [
        'dao' => 'CRM_Activity_DAO_Activity',
        // 'extends' => 'Activity',
        'fields' => [
          'custom_40' => [
            'title' => ts('Presentation Topics'),
            'required' => TRUE,
            'dbAlias' => 'presentation_topics_40'
          ],
          'custom_332' => [
            'title' => ts('Facilitating Program'),
            'required' => TRUE,
            'dbAlias' => 'facilitating_program_332',
          ],
          'custom_88' => [
            'title' => ts('Presentation Method'),
            'required' => TRUE,
            'dbAlias' => 'contact_method_88',
          ],
          'custom_90' => [
            'title' => ts('Youth or Adult'),
            'dbAlias' => 'youth_or_adult_90',
            'required' => TRUE,
          ],
          'custom_341' => [
            'title' => ts('Audience'),
            'required' => TRUE,
            'dbAlias' => 'audience_341',
          ],
          'custom_815' => [
            'title' => ts('Number of Participants'),
            'required' => TRUE,
            'dbAlias' => 'number_of_participants_per_cours_815',
          ],
        ],
        'filters' => [],
      ],
      'civicrm_contact_sc' => [
        'dao'     => 'CRM_Contact_DAO_Contact',
        'fields' => [
          'presenter_1'   => [
            'title'     => ts('Presenter 1'),
            'alias'     => 'sc',
            'required' => TRUE,
            'dbAlias' => "GROUP_CONCAT(CONCAT(sc.id, '-', sc.display_name))",
          ],
          'presenter_2'   => [
            'title'     => ts('Presenter 2'),
            'alias'     => 'sc',
            'required' => TRUE,
            'dbAlias' => "1",
          ],
          'presenter_3'   => [
            'title'     => ts('Presenter 3'),
            'alias'     => 'sc',
            'required' => TRUE,
            'dbAlias' => "1",
          ],
          'presenter_4'   => [
            'title'     => ts('Presenter 4'),
            'alias'     => 'sc',
            'required' => TRUE,
            'dbAlias' => '1',
          ],
        ],
      ],
      'civicrm_activity' => [
        'dao' => 'CRM_Activity_DAO_Activity',
        'fields' => [
          'id' => [
            'no_display' => TRUE,
            'title' => ts('Activity ID'),
            'required' => TRUE,
          ],
          'duration' => [
            'title' => ts('Length (in minutes)'),
            'required' => TRUE,
          ],
          /*
          'activity_type_id' => [
            // 'no_display' => TRUE,
            'title' => ts('Activity Type ID'),
            'required' => TRUE,
          ],
          'activity_date_time' => [
            // 'no_display' => TRUE,
            'title' => ts('Activity Date'),
            'required' => TRUE,
            'dbAlias' => 'DATE(activity_date_time)'
          ],
          */
        ],
        'filters' => [
          'activity_date_time' => [
            'type' => CRM_Utils_Type::T_DATE,
            'title' => E::ts('Activity date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
          ],
        ],
      ],
/*
      'civicrm_contact_ac' => [
        'dao'     => 'CRM_Contact_DAO_Contact',
        'fields' => [
          'display_name'   => [
            'title'     => ts('Booking Contact'),
            'alias'     => 'ac',
            'required' => TRUE,
            'dbAlias' => "GROUP_CONCAT(DISTINCT ac.display_name)",
          ],
        ],
      ],
*/
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
            'required' => TRUE,
            'dbAlias' => "tc_add.postal_code",
          ],
        ],
      ],
    ];

    $mapping = \Civi\Api4\OptionValue::get(FALSE)
      ->addSelect('value', 'label')
      ->addWhere('option_group_id:label', '=', 'PED Evaluation Question mapping')
      ->execute();
    $questionMapper = [];
    foreach($mapping as $value) {
      $questionMapper[$value['label']] = $value['value'];
    }
    $cf = \Civi\Api4\CustomField::get(FALSE)
      ->addSelect('html_type', 'label')
      ->addWhere('custom_group_id:name', '=', 'PED_Participant_Presentation_Feedback')
      ->execute();
    foreach($cf as $value) {
      if ($key = array_search($value['id'], $questionMapper)) {
        $questionMapper[$key] = [
          'type' => $value['html_type'],
          'label' => $value['label'],
          'options' => [],
        ];
      }
    }

    $cg = \Civi\Api4\CustomGroup::get(FALSE)
      ->addSelect('table_name')
      ->addWhere('name', '=', 'PED_Presentation_Evaluation_Summary_Score')
      ->execute()->first();
    $this->_columns[$cg['table_name']] = [
      'dao' => 'CRM_Activity_DAO_Activity',
      'fields' => [],
      'filters' => [],
    ];
    $customFields = \Civi\Api4\CustomField::get(FALSE)
      ->addSelect('label', 'html_type', 'column_name')
      ->addWhere('custom_group_id', '=', $cg['id'])
      ->execute();
    foreach ($customFields as $customField) {
      if (strstr($customField['label'], 'SUM_') && !strstr($customField['label'], 'EVAL_SUM_') && !strstr($customField['label'], 'STAFF_EVAL_SUM_')) {
        $q = preg_replace("/[^0-9]/", '', $customField['label']);
        $option = str_replace('SUM_' . $q, '', $customField['label']);
        $questionMapper[$q]['options'][$option] = ['id' => $customField['id'], 'column_name' => $customField['column_name']];
      }
      if (strstr($customField['label'], 'EVAL_SUM_') || strstr($customField['label'], 'STAFF_EVAL_SUM_')) {
        $q = str_replace('STAFF_', '', str_replace('EVAL_SUM_', '', $customField['label']));
        if ($questionMapper[$q]['type'] == 'TextArea') {
          $questionMapper[$q]['options']['total'] = ['id' => $customField['id'], 'column_name' => $customField['column_name']];
        }
      }
    }

    $this->_mapper = $questionMapper;

    foreach ($questionMapper as $Q => $mapper) {
      if ($Q == 211 || $Q == 26) {continue;}
      if ($mapper['type'] == 'Radio') {
        foreach ([
          'SA' => 'Strongly Agree',
          'A' => 'Agree',
          'SWA' => 'Somewhat Agree',
          'SWD' => 'Somewhat Disagree',
          'D' => 'Disagree',
          'SD' => 'Strongly Disagree',
        ] as $key => $label) {
          $this->_columns[$cg['table_name']]['fields'][$mapper['options'][$key]['column_name']] = [
            'title' => 'Q' . $Q . ' ' . $label,
            'required' => TRUE,
          ];
        }
      }
      if ($mapper['type'] == 'TextArea') {
       $this->_columns[$cg['table_name']]['fields'][$mapper['options']['total']['column_name']] = [
         'title' => 'Q' . $Q . ' ' . 'Number Surveyed Able to List Something Learned',
         'required' => TRUE,
       ];
      }
    }
//CRM_Core_Error::Debug('a', $this->_columns);
//    $this->_groupFilter = TRUE;
//    $this->_tagFilter = TRUE;
//    CRM_Publicedbookingsreport_Utils::addFilter($this->_columns['civicrm_value_booking_infor_2']['filters'], ['presentation_topics_40','contact_method_88','facilitating_program_332','youth_or_adult_90','audience_341']);

    parent::__construct();
  }

  public function preProcess() {
    $this->assign('reportTitle', E::ts('Public Ed Bookings Report'));
    parent::preProcess();
  }

  public function from() {
    $this->_from = "
      FROM civicrm_value_ped_booking_r_53 {$this->_aliases['civicrm_value_ped_booking_r_53']}
      INNER JOIN civicrm_activity {$this->_aliases['civicrm_activity']} ON {$this->_aliases['civicrm_activity']}.id = {$this->_aliases['civicrm_value_ped_booking_r_53']}.booking_reference_id_706
      INNER JOIN civicrm_value_ped_presentat_54 {$this->_aliases['civicrm_value_ped_presentat_54']} ON {$this->_aliases['civicrm_value_ped_presentat_54']}.entity_id = {$this->_aliases['civicrm_value_ped_booking_r_53']}.entity_id
      LEFT JOIN civicrm_value_booking_infor_2 {$this->_aliases['civicrm_value_booking_infor_2']} ON {$this->_aliases['civicrm_value_booking_infor_2']}.entity_id = {$this->_aliases['civicrm_value_ped_booking_r_53']}.booking_reference_id_706
      LEFT JOIN civicrm_activity_contact assignee ON assignee.activity_id = {$this->_aliases['civicrm_value_ped_booking_r_53']}.booking_reference_id_706 AND assignee.record_type_id = 2
      LEFT JOIN civicrm_activity_contact target ON target.activity_id = {$this->_aliases['civicrm_value_ped_booking_r_53']}.booking_reference_id_706 AND target.record_type_id = 3
      LEFT JOIN civicrm_activity_contact source ON source.activity_id = {$this->_aliases['civicrm_value_ped_booking_r_53']}.booking_reference_id_706 AND source.record_type_id = 1
      LEFT JOIN civicrm_contact ac ON ac.id = assignee.contact_id
      LEFT JOIN civicrm_contact tc ON tc.id = target.contact_id
      LEFT JOIN civicrm_address tc_add ON tc_add.contact_id = tc.id AND tc_add.is_primary = 1
      LEFT JOIN civicrm_contact sc ON sc.id = source.contact_id
    ";

  }

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
/*
  public function where() {
    $this->_where = "WHERE {$this->_aliases['civicrm_activity']}.activity_type_id = 200";
  }
*/
  public function alterDisplay(&$rows) {
    //print_r($this->_columnHeaders);
    $count = 1;
    $CH2 = ['PI' => ['title' => 'Presentation Information', 'colspan' => $count]];
    foreach ($this->_columnHeaders as $key => $header) {
      if ($key != 'civicrm_value_ped_presentat_54_sum_1sa_48' && $count != 0) {
        $count++;
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_1sa_48') {
       $CH2['PI']['colspan'] = $count - 1;
       $count = 0;
       $CH2['Q1'] = ['title' => $this->_mapper[1]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_staff_eval_sum_2_1275') {
        $CH2['Q2'] = ['title' => $this->_mapper[2]['label'], 'colspan' => 1];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_3sa_60') {
     $CH2['Q3'] = ['title' => $this->_mapper[3]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_5sa_68') {
       $CH2['Q4'] = ['title' => $this->_mapper[4]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_staff_eval_sum_5_1276') {
       $CH2['Q5'] = ['title' => $this->_mapper[5]['label'], 'colspan' => 1];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_7sa_75') {
       $CH2['Q6'] = ['title' => $this->_mapper[6]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_8sa_81') {
       $CH2['Q7'] = ['title' => $this->_mapper[7]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_8sa_716') {
       $CH2['Q8'] = ['title' => $this->_mapper[8]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_staff_eval_sum_9_1277') {
       $CH2['Q9'] = ['title' => $this->_mapper[9]['label'], 'colspan' => 1];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_10sa_725') {
       $CH2['Q10'] = ['title' => $this->_mapper[10]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_11sa_732') {
       $CH2['Q11'] = ['title' => $this->_mapper[11]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_12sa_739') {
       $CH2['Q12'] = ['title' => $this->_mapper[12]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_13sa_746') {
       $CH2['Q13'] = ['title' => $this->_mapper[13]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_14sa_753') {
       $CH2['Q14'] = ['title' => $this->_mapper[14]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_15sa_760') {
       $CH2['Q15'] = ['title' => $this->_mapper[15]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_16sa_767') {
       $CH2['Q16'] = ['title' => $this->_mapper[16]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_17sa_774') {
       $CH2['Q17'] = ['title' => $this->_mapper[17]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_staff_eval_sum_18_1278') {
       $CH2['Q18'] = ['title' => $this->_mapper[18]['label'], 'colspan' => 1];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_18sa_781') {
       $CH2['Q19'] = ['title' => $this->_mapper[19]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_20sa_789') {
       $CH2['Q20'] = ['title' => $this->_mapper[20]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_staff_eval_sum_22_1279') {
       $CH2['Q22'] = ['title' => $this->_mapper[22]['label'], 'colspan' => 1];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_staff_eval_sum_23_1280') {
       $CH2['Q23'] = ['title' => $this->_mapper[23]['label'], 'colspan' => 1];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_25sa_1268') {
       $CH2['Q212'] = ['title' => $this->_mapper[212]['label'], 'colspan' => 6];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_24sa_805') {
       $CH2['Q24'] = ['title' => $this->_mapper[24]['label'], 'colspan' => 6];
      }
    }
    $this->assign('_columnHeaders1', $CH2);
    foreach ($rows as $rowNum => &$row) {
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
