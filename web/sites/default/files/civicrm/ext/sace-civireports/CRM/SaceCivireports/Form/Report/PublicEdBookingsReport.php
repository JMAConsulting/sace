<?php
use CRM_SaceCivireports_ExtensionUtil as E;

class CRM_SaceCivireports_Form_Report_PublicEdBookingsReport extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE;

  public $_mapper = NULL;

  protected $_customGroupExtends = 'Activity';

  public $_demographicFields = [];
  public $_audienceFields = [];
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
          'participant_count' => [
            'title' => ts('Number of participants'),
            'dbAlias' => 'GROUP_CONCAT(DISTINCT number_of_students_per_session_24)',
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

    $mapping = \Civi\Api4\OptionValue::get(FALSE)
      ->addSelect('value', 'label')
      ->addWhere('option_group_id:label', '=', 'PED Evaluation Question mapping')
      ->addWhere('is_active', '=', TRUE)
      ->addOrderBy('weight', 'ASC')
      ->execute();
    $questionMapper = [];
    foreach($mapping as $value) {
      $questionMapper[$value['label']] = $value['value'];
      if (in_array($value['label'], [2, 5, 9, 18, 22, 23])) {
        $questionMapper[$value['label'] . '_staff'] = $value['value'];
        $questionMapper[$value['label'] . '_note'] = $value['value'];
      }
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
        if ($value['html_type'] == 'TextArea') {
          $questionMapper[$key . '_staff'] = [
            'type' => $value['html_type'],
            'label' => $value['label'],
            'options' => [],
          ];
          $questionMapper[$key . '_note'] = [
            'type' => $value['html_type'],
            'label' => $value['label'],
            'options' => [],
          ];
        }
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
      if ('Age - Qualitative staff evaluation' == $customField['label']) {
        $this->_columns[$cg['table_name']]['fields'][$customField['column_name']] = ['title' => $customField['label'], 'required' => 0];
      }
      if ($customField['label'] == 'Number of participants') {
         $this->_columns[$cg['table_name']]['fields'][$customField['column_name']] = ['title' => $customField['label'], 'required' => 0, 'no_display' => 1];
      }
      if (strstr($customField['label'], 'SUM_') && !strstr($customField['label'], 'EVAL_SUM_') && !strstr($customField['label'], 'STAFF_EVAL_SUM_')) {
        $q = preg_replace("/[^0-9]/", '', $customField['label']);
        $option = str_replace('SUM_' . $q, '', $customField['label']);
        $questionMapper[$q]['options'][$option] = ['id' => $customField['id'], 'column_name' => $customField['column_name']];
      }
      if (strstr($customField['label'], 'EVAL_SUM_') && !strstr($customField['label'], 'STAFF_EVAL_SUM_')) {
        $q = str_replace('EVAL_SUM_', '', $customField['label']);
        if ($questionMapper[$q]['type'] == 'TextArea') {
          $questionMapper[$q]['options']['total'] = ['id' => $customField['id'], 'column_name' => $customField['column_name']];
        }
      }
      if (strstr($customField['label'], 'STAFF_EVAL_SUM_')) {
        $q = str_replace('STAFF_', '', str_replace('EVAL_SUM_', '', $customField['label']));
        if ($questionMapper[$q]['type'] == 'TextArea') {
          $questionMapper[$q . '_staff']['options']['total'] = ['id' => $customField['id'], 'column_name' => $customField['column_name'], 'dbAlias' => $customField['column_name']];
        }
      }
      if (strstr($customField['label'], "Noteworthy")) {
        $q = trim(str_replace('Q', '', str_replace('Noteworthy comments from evaluations', '', $customField['label'])));
        if ($questionMapper[$q]['type'] == 'TextArea') {
          $questionMapper[$q . '_note']['options']['total'] = ['id' => $customField['id'], 'column_name' => $customField['column_name'], 'dbAlias' => $customField['column_name']];
        }
      }
    }

    $this->_mapper = $questionMapper;
    foreach ($questionMapper as $Q => $mapper) {
      if ($mapper['type'] == 'Radio') {
        $dbAlias = [];
        foreach ([
          'SA' => 'Strongly Agree',
          'A' => 'Agree',
          'SWA' => 'Somewhat Agree',
          'SWD' => 'Somewhat Disagree',
          'D' => 'Disagree',
          'SD' => 'Strongly Disagree',
        ] as $key => $label) {
          $this->_columns[$cg['table_name']]['fields'][$mapper['options'][$key]['column_name']] = [
            'title' => $label,
            'required' => FALSE,
            'dbAlias' => "COALESCE(" . $mapper['options'][$key]['column_name'] . ",0)",
          ];
          $dbAlias[] = "COALESCE(" . $mapper['options'][$key]['column_name'] . ",0)";
        }
        $this->_columns[$cg['table_name']]['fields'][$Q . '_responses'] = [
        'title' => 'Number of respondents',
        'required' => FALSE,
        'dbAlias' => sprintf("(%s)", implode(' + ', $dbAlias)),
        ];
      }
      if ($mapper['type'] == 'TextArea') {
       $this->_columns[$cg['table_name']]['fields'][$mapper['options']['total']['column_name']] = [
         'title' => (strstr($Q, '_staff') ? 'Qualitative staff evaluations' : (strstr($Q, '_note') ? 'Noteworthy comments from evaluations' : 'Number Surveyed Able to List Something Learned')),
         'required' => FALSE,
       ];
       if (strstr($Q, '_note')) {
          $this->_columns[$cg['table_name']]['fields'][str_replace('_note', '', $Q) . '_responses'] = [
            'title' => 'Number of respondents',
            'required' => FALSE,
            'dbAlias' => sprintf("(COALESCE(%s, 0) + COALESCE(%s, 0))", $questionMapper[str_replace('_note', '', $Q)]['options']['total']['column_name'], $questionMapper[str_replace('_note', '', $Q) . '_staff']['options']['total']['column_name']),
          ];
       }
       if (!empty($mapper['options']['total']['dbAlias'])) {
         $this->_columns[$cg['table_name']]['fields'][$mapper['options']['total']['column_name']]['dbAlias'] = $mapper['options']['total']['dbAlias'];
       }
      }
    }
//CRM_Core_Error::debug('questionMapper', $this->_columns[$cg['table_name']]['fields']);

    parent::__construct();
    $keys = ['civicrm_value_ped_booking_r_53', 'civicrm_value_booking_infor_2', 'civicrm_contact_sc', 'civicrm_activity', 'civicrm_contact_tc', 'civicrm_value_ped_presentat_54'];
    foreach (array_keys($this->_columns) as $key) {
      if (!in_array($key, $keys)) {unset($this->_columns[$key]);}
    }
    foreach (['custom_332'] as $key) {
      $this->_columns['civicrm_value_booking_infor_2']['fields'][$key]['required'] = 1;
    }
    foreach (array_keys($this->_columns['civicrm_value_ped_presentat_54']['fields']) as $key) {
      if (strstr($key, 'custom_')) {
        unset($this->_columns['civicrm_value_ped_presentat_54']['fields'][$key]);
        unset($this->_columns['civicrm_value_ped_presentat_54']['filters'][$key]);
      }
    }

    $this->_columns['civicrm_value_ped_participa_49'] = [
      'extends' => 'Activity',
      'group_title' => 'PE - Participant Presentation Feedback',
      'fields' => [
        'age' => [
          'title' => ts('Age (Demographic information open field)'),
          'dbAlias' => 'GROUP_CONCAT(DISTINCT CONCAT(pp.entity_id, "-", pp.age_1263))',
        ],
        'demo_questions' => [
          'title' => ts('Number of respondents to demo questions'),
          'dbAlias' => '0',
        ]
      ],
    ];
    $values = \Civi\Api4\OptionValue::get(FALSE)
      ->addSelect('value', 'label', 'name')
      ->addWhere('option_group_id', '=', 111)
      ->execute();
    foreach ($values as $value) {
      $this->_columns['civicrm_value_ped_participa_49']['fields'][$value['name']] = [
        'title' => sprintf('Demographics - %s', $value['label']),
        'dbAlias' => '0',
        'type' => CRM_Utils_Type::T_INT,
      ];
      $this->_demographicFields[$value['value']] = $value['name'];
    }

    unset($this->_columns['civicrm_value_booking_infor_2']['fields']['custom_341']);
    $values = \Civi\Api4\OptionValue::get(FALSE)
      ->addSelect('value', 'label', 'name')
      ->addWhere('option_group_id', '=', 234)
      ->addWhere('is_active', '=', TRUE)
      ->execute();
    foreach ($values as $value) {
      $this->_columns['civicrm_value_booking_infor_2']['fields']['audience_' . $value['name']] = [
        'title' => sprintf('Audience - %s', $value['label']),
        'dbAlias' => '0',
        'type' => CRM_Utils_Type::T_INT,
      ];
      $this->_audienceFields['audience_' . $value['value']] = $value['name'];
    }

    $lastkey = $this->_columns['civicrm_value_ped_presentat_54'];
    unset($this->_columns['civicrm_value_ped_presentat_54']);
    $this->_columns['civicrm_value_ped_presentat_54'] = $lastkey;
  }

public function addCustomDataToColumns($addFields = TRUE, $permCustomGroupIds = []) {
  parent::addCustomDataToColumns($addFields, $permCustomGroupIds);
}

  public function preProcess() {
    $this->assign('reportTitle', E::ts('Public Ed Bookings Report'));
    parent::preProcess();
  }

  public function from() {
    $this->createTemporaryTable('max_activities',
      "SELECT max(a.id) as id, max(a.activity_date_time) AS activity_date, bf.booking_reference_id_706
        FROM civicrm_activity a
        INNER JOIN civicrm_value_ped_booking_r_53 bf ON bf.entity_id = a.id
        WHERE a.activity_type_id IN (200)
        GROUP BY bf.booking_reference_id_706"
    );
    $this->_from = "
      FROM civicrm_activity {$this->_aliases['civicrm_activity']}
      INNER JOIN (
        SELECT a.id AS entity_id, m.booking_reference_id_706
        FROM civicrm_activity a
        INNER JOIN {$this->temporaryTables['max_activities']['name']} as m ON m.activity_date = a.activity_date_time AND m.id = a.id
      ) summary ON summary.booking_reference_id_706 = {$this->_aliases['civicrm_activity']}.id
      INNER JOIN civicrm_value_ped_booking_r_53 {$this->_aliases['civicrm_value_ped_booking_r_53']} ON {$this->_aliases['civicrm_value_ped_booking_r_53']}.entity_id = summary.entity_id
      LEFT JOIN civicrm_value_ped_presentat_54 {$this->_aliases['civicrm_value_ped_presentat_54']} ON {$this->_aliases['civicrm_value_ped_presentat_54']}.entity_id = summary.entity_id
      LEFT JOIN civicrm_value_booking_infor_2 {$this->_aliases['civicrm_value_booking_infor_2']} ON {$this->_aliases['civicrm_value_booking_infor_2']}.entity_id = {$this->_aliases['civicrm_value_ped_booking_r_53']}.booking_reference_id_706
      LEFT JOIN civicrm_activity_contact assignee ON assignee.activity_id = {$this->_aliases['civicrm_value_ped_booking_r_53']}.booking_reference_id_706 AND assignee.record_type_id = 2
      LEFT JOIN civicrm_activity_contact target ON target.activity_id = {$this->_aliases['civicrm_value_ped_booking_r_53']}.booking_reference_id_706 AND target.record_type_id = 3
      LEFT JOIN civicrm_activity_contact source ON source.activity_id = {$this->_aliases['civicrm_value_ped_booking_r_53']}.booking_reference_id_706 AND source.record_type_id = 1
      LEFT JOIN civicrm_contact ac ON ac.id = assignee.contact_id
      LEFT JOIN civicrm_contact tc ON tc.id = target.contact_id
      LEFT JOIN civicrm_address tc_add ON tc_add.contact_id = tc.id AND tc_add.is_primary = 1
      LEFT JOIN civicrm_entity_tag tc_entity_tag ON tc_entity_tag.entity_id = tc.id AND tc_entity_tag.entity_table = 'civicrm_contact'
      LEFT JOIN civicrm_tag tc_tag ON tc_tag.id = tc_entity_tag.tag_id
      LEFT JOIN civicrm_contact sc ON sc.id = source.contact_id
      LEFT JOIN (
        SELECT entity_id, booking_reference_id_706
        FROM civicrm_value_ped_booking_r_53 bf
        INNER JOIN civicrm_activity a ON a.id = bf.entity_id AND a.activity_type_id IN (197)
      ) feedback ON feedback.booking_reference_id_706 = {$this->_aliases['civicrm_activity']}.id
      LEFT JOIN civicrm_value_ped_participa_49 pp ON pp.entity_id = feedback.entity_id
   ";

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
    if (!empty($this->_params['fields']['demo_questions']) && !empty($selectedAids)) {
      $sql = sprintf("SELECT  count(pp.entity_id) as count, pb.booking_reference_id_706 as aid
            FROM civicrm_value_booking_infor_2 bi
            INNER JOIN civicrm_value_ped_booking_r_53 pb ON pb.booking_reference_id_706 = bi.entity_id
            INNER JOIN civicrm_value_ped_participa_49 pp ON pp.entity_id = pb.entity_id
            WHERE demographic_information_1262 <> '' AND pb.booking_reference_id_706 IN (%s)
            GROUP BY pb.booking_reference_id_706" , implode(',', $selectedAids));
      $this->addToDeveloperTab($sql);
      $result = CRM_Core_DAO::executeQuery($sql)->fetchAll();
      foreach ($result as $value) {
        if (!empty($value['count'])) {
          $key = array_search($value['aid'], $selectedAids);
          $rows[$key]['civicrm_value_ped_participa_49_demo_questions'] = $value['count'];
        }
      }
    }
    foreach ($this->_demographicFields as $ov => $name) {
      if (!empty($this->_params['fields'][$name]) && !empty($selectedAids)) {
        $sql = sprintf("SELECT  count(pp.entity_id) as count, pb.booking_reference_id_706 as aid
        FROM civicrm_value_booking_infor_2 bi
        INNER JOIN civicrm_value_ped_booking_r_53 pb ON pb.booking_reference_id_706 = bi.entity_id
        INNER JOIN civicrm_value_ped_participa_49 pp ON pp.entity_id = pb.entity_id
        WHERE demographic_information_1262 REGEXP '([[:cntrl:]]|^){$ov}([[:cntrl:]]|$)' AND pb.booking_reference_id_706 IN (%s)
        GROUP BY pb.booking_reference_id_706" , implode(',', $selectedAids));
        $this->addToDeveloperTab($sql);
        $result = CRM_Core_DAO::executeQuery($sql)->fetchAll();
        foreach ($result as $value) {
	   if (!empty($value['count'])) {
             $key = array_search($value['aid'], $selectedAids);
             $rows[$key]['civicrm_value_ped_participa_49_' . $name] = $value['count'];
           }
        }
      }
    }


  foreach ($this->_audienceFields as $ov => $name) {
  if (!empty($this->_params['fields'][$name]) && !empty($selectedAids)) {
    $sql = sprintf("SELECT count(entity_id) as count, entity_id as aid
    FROM civicrm_value_booking_infor_2
    WHERE audience_341 REGEXP '([[:cntrl:]]|^){$ov}([[:cntrl:]]|$)' AND entity_id IN (%s)
    GROUP BY entity_id" , implode(',', $selectedAids));
    $this->addToDeveloperTab($sql);
    $result = CRM_Core_DAO::executeQuery($sql)->fetchAll();
    foreach ($result as $value) {
       if (!empty($value['count'])) {
         $key = array_search($value['aid'], $selectedAids);
         $rows[$key]['civicrm_value_booking_infor_2_' . $name] = $value['count'];
       }
    }
  }
  }
    $count = 1;
    $CH2 = ['PI' => ['title' => 'Presentation Information', 'colspan' => $count]];
    foreach ($this->_columnHeaders as $key => $header) {
      if ($key != 'civicrm_value_ped_presentat_54_sum_1sa_48' && $count != 0) {
        $count++;
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_1sa_48') {
       $CH2['PI']['colspan'] = $count - 1;
       $count = 0;
       $CH2['Q1'] = ['title' => $this->_mapper[1]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_2_59') {
        $CH2['Q2'] = ['title' => $this->_mapper[2]['label'], 'colspan' => 4];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_3sa_60') {
        $CH2['Q3'] = ['title' => $this->_mapper[3]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_5sa_68') {
       $CH2['Q4'] = ['title' => $this->_mapper[4]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_staff_eval_sum_5_1276') {
       $CH2['Q5'] = ['title' => $this->_mapper[5]['label'], 'colspan' => 4];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_7sa_75') {
       $CH2['Q6'] = ['title' => $this->_mapper[6]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_8sa_81') {
       $CH2['Q7'] = ['title' => $this->_mapper[7]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_8sa_716') {
       $CH2['Q8'] = ['title' => $this->_mapper[8]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_staff_eval_sum_9_1277') {
       $CH2['Q9'] = ['title' => $this->_mapper[9]['label'], 'colspan' => 4];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_10sa_725') {
       $CH2['Q10'] = ['title' => $this->_mapper[10]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_11sa_732') {
       $CH2['Q11'] = ['title' => $this->_mapper[11]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_12sa_739') {
       $CH2['Q12'] = ['title' => $this->_mapper[12]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_13sa_746') {
       $CH2['Q13'] = ['title' => $this->_mapper[13]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_14sa_753') {
       $CH2['Q14'] = ['title' => $this->_mapper[14]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_15sa_760') {
       $CH2['Q15'] = ['title' => $this->_mapper[15]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_16sa_767') {
       $CH2['Q16'] = ['title' => $this->_mapper[16]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_17sa_774') {
       $CH2['Q17'] = ['title' => $this->_mapper[17]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_staff_eval_sum_18_1278') {
       $CH2['Q18'] = ['title' => $this->_mapper[18]['label'], 'colspan' => 4];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_18sa_781') {
       $CH2['Q19'] = ['title' => $this->_mapper[19]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_20sa_789') {
       $CH2['Q20'] = ['title' => $this->_mapper[20]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_21sa_796') {
       $CH2['Q211'] = ['title' => $this->_mapper[211]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_25sa_1268') {
       $CH2['Q212'] = ['title' => $this->_mapper[212]['label'], 'colspan' => 7];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_staff_eval_sum_22_1279') {
       $CH2['Q22'] = ['title' => $this->_mapper[22]['label'], 'colspan' => 4];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_staff_eval_sum_23_1280') {
       $CH2['Q23'] = ['title' => $this->_mapper[23]['label'], 'colspan' => 4];
      }
      elseif ($key == 'civicrm_value_ped_presentat_54_sum_24sa_805') {
       $CH2['Q24'] = ['title' => $this->_mapper[24]['label'], 'colspan' => 7];
      }
    }

    $this->assign('_columnHeaders1', $CH2);
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
