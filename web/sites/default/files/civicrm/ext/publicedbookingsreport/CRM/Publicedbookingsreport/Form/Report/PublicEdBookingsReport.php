<?php
use CRM_Publicedbookingsreport_ExtensionUtil as E;

class CRM_Publicedbookingsreport_Form_Report_PublicEdBookingsReport extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE;

  /**
   * protected $_customGroupExtends = ['Activity'];
   */
  public function __construct() {
    $this->_columns = [
      'civicrm_value_ped_booking_r_53' => [
        'dao' => 'CRM_Activity_DAO_Activity',
        'fields' => [
          'booking_reference_id_706' => [
            'title' => ts('Booking Ref'),
            'required' => TRUE,
            'no_display' => TRUE,
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
          'activity_date_time_Month' => [
            'title' => ts('Activity Month'),
            'required' => TRUE,
            'dbAlias' => 'MONTH(activity_date_time)',
          ],
          'activity_date_time_Year' => [
            'title' => ts('Activity Year'),
            'required' => TRUE,
            'dbAlias' => 'Year(activity_date_time)',
          ],
        ],
      ],
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
      'civicrm_contact_tc' => [
        'dao'     => 'CRM_Contact_DAO_Contact',
        'fields' => [
          'display_name'   => [
            'title'     => ts('School/Organization'),
            'alias'     => 'tc',
            'required' => TRUE,
            'dbAlias' => "GROUP_CONCAT(DISTINCT tc.display_name)",
          ],
        ],
      ],
      'civicrm_contact_sc' => [
        'dao'     => 'CRM_Contact_DAO_Contact',
        'fields' => [
          'display_name'   => [
            'title'     => ts('Staff Assigned'),
            'alias'     => 'sc',
            'required' => TRUE,
            'dbAlias' => "GROUP_CONCAT(DISTINCT sc.display_name)",
          ],
        ],
      ],

      'civicrm_value_booking_infor_2' => [
        'dao' => 'CRM_Contact_DAO_Contact',
        'extends' => 'Activity',
        'fields' => [
          'presentation_topics_40' => [
            'title' => ts('Presentation Topics'),
            'required' => TRUE,
          ],
          'facilitating_program_332' => [
            'title' => ts('Facilitating Program'),
            'required' => TRUE,
          ],
          'contact_method_88' => [
            'title' => ts('Delivery Format'),
            'required' => TRUE,
          ],
          'youth_or_adult_90' => [
            'title' => ts('Youth or Adult'),
            'required' => TRUE,
          ],
          'audience_341' => [
            'title' => ts('Audience'),
            'required' => TRUE,
          ],
          'number_of_participants_per_cours_815' => [
            'title' => ts('No. of Participants'),
            'required' => TRUE,
          ],
        ],
      ],

      'civicrm_value_ped_presentat_54' => [
        'dao' => 'CRM_Activity_DAO_Activity',
        'fields' => [
          'number_of_online_evaluations_458' => [
            'title' => ts('No. of Online Evaluations'),
            'required' => TRUE,
          ],
          'number_of_staff_entered_evaluati_459' => [
            'title' => ts('No. of Staff Entered Evaluations'),
            'required' => TRUE,
          ],
              //Q1
          'sum_1sa_48' => [
            'title' => ts('Q1 SA'),
            'required' => TRUE,
          ],
          'sum_1a_54' => [
            'title' => ts('Q1 A'),
            'required' => TRUE,
          ],
          'sum_1n_55' => [
            'title' => ts('Q1 SWA'),
            'required' => TRUE,
          ],
          'sum_1swd_707' => [
            'title' => ts('Q1 SWD'),
            'required' => TRUE,
          ],
          'sum_1d_56' => [
            'title' => ts('Q1 D'),
            'required' => TRUE,
          ],
          'sum_1sd_57' => [
            'title' => ts('Q1 SD'),
            'required' => TRUE,
          ],
          'total_1_58' => [
            'title' => ts('Q1 Total'),
            'required' => TRUE,
          ],
              //Q2
          'sum_2_59' => [
            'title' => ts('Q2 Total'),
            'required' => TRUE,
          ],
          'staff_eval_sum_2_1275' => [
            'title' => ts('Q2 Staff Evaluation'),
            'required' => TRUE,
          ],
              //Q3
          'sum_3sa_60' => [
            'title' => ts('Q3 SA'),
            'required' => TRUE,
          ],
          'sum_3a_61' => [
            'title' => ts('Q3 A'),
            'required' => TRUE,
          ],
          'sum_3n_62' => [
            'title' => ts('Q3 SWA'),
            'required' => TRUE,
          ],
          'sum_3swd_709' => [
            'title' => ts('Q3 SWD'),
            'required' => TRUE,
          ],
          'sum_3d_63' => [
            'title' => ts('Q3 D'),
            'required' => TRUE,
          ],
          'sum_3sd_64' => [
            'title' => ts('Q3 SD'),
            'required' => TRUE,
          ],
          'total_3_67' => [
            'title' => ts('Q3 Total'),
            'required' => TRUE,
          ],
              //Q4
          'sum_5sa_68' => [
            'title' => ts('Q4 SA'),
            'required' => TRUE,
          ],
          'sum_5a_69' => [
            'title' => ts('Q4 A'),
            'required' => TRUE,
          ],
          'sum_5n_70' => [
            'title' => ts('Q4 SWA'),
            'required' => TRUE,
          ],
          'sum_5swd_711' => [
            'title' => ts('Q4 SWD'),
            'required' => TRUE,
          ],
          'sum_5d_71' => [
            'title' => ts('Q4 D'),
            'required' => TRUE,
          ],
          'sum_5sd_72' => [
            'title' => ts('Q4 SD'),
            'required' => TRUE,
          ],
          'total_5_73' => [
            'title' => ts('Q4 Total'),
            'required' => TRUE,
          ],
              //Q5
          'sum_4_65' => [
            'title' => ts('Q5 Total'),
            'required' => TRUE,
          ],
          'staff_eval_sum_5_1276' => [
            'title' => ts('Q5 Staff Evaluation'),
            'required' => TRUE,
          ],
              //Q6
          'sum_7sa_75' => [
            'title' => ts('Q6 SA'),
            'required' => TRUE,
          ],
          'sum_7a_76' => [
            'title' => ts('Q6 A'),
            'required' => TRUE,
          ],
          'sum_7n_77' => [
            'title' => ts('Q6 SWA'),
            'required' => TRUE,
          ],
          'sum_7swd_712' => [
            'title' => ts('Q6 SWD'),
            'required' => TRUE,
          ],
          'sum_7d_78' => [
            'title' => ts('Q6 D'),
            'required' => TRUE,
          ],
          'sum_7sd_79' => [
            'title' => ts('Q6 SD'),
            'required' => TRUE,
          ],
          'sum_6_74' => [
            'title' => ts('Q6 Total'),
            'required' => TRUE,
          ],
              //Q7
          'sum_8sa_81' => [
            'title' => ts('Q8 SA'),
            'required' => TRUE,
          ],
          'sum_8a_82' => [
            'title' => ts('Q7 A'),
            'required' => TRUE,
          ],
          'sum_8n_83' => [
            'title' => ts('Q7 SWA'),
            'required' => TRUE,
          ],
          'sum_8swd_713' => [
            'title' => ts('Q7 SWD'),
            'required' => TRUE,
          ],
          'sum_8d_84' => [
            'title' => ts('Q7 D'),
            'required' => TRUE,
          ],
          'sum_8sd_85' => [
            'title' => ts('Q7 SD'),
            'required' => TRUE,
          ],
          'eval_sum_7_723' => [
            'title' => ts('Q7 Total'),
            'required' => TRUE,
          ],
              //Q8
          'sum_8sa_716' => [
            'title' => ts('Q8 SA'),
            'required' => TRUE,
          ],
          'sum_8a_717' => [
            'title' => ts('Q8 A'),
            'required' => TRUE,
          ],
          'sum_8swa_718' => [
            'title' => ts('Q8 SWA'),
            'required' => TRUE,
          ],
          'sum_8swd_719' => [
            'title' => ts('Q8 SWD'),
            'required' => TRUE,
          ],
          'sum_8d_720' => [
            'title' => ts('Q8 D'),
            'required' => TRUE,
          ],
          'sum_8sd_721' => [
            'title' => ts('Q8 SD'),
            'required' => TRUE,
          ],
          'eval_sum_8_722' => [
            'title' => ts('Q8 Total'),
            'required' => TRUE,
          ],
              //Q9
          'eval_sum_9_724' => [
            'title' => ts('Q9 Total'),
            'required' => TRUE,
          ],
          'staff_eval_sum_9_1277' => [
            'title' => ts('Q9 Staff Evaluation'),
            'required' => TRUE,
          ],
              //Q10
          'sum_10sa_725' => [
            'title' => ts('Q10 SA'),
            'required' => TRUE,
          ],
          'sum_10a_726' => [
            'title' => ts('Q10 A'),
            'required' => TRUE,
          ],
          'sum_10swa_727' => [
            'title' => ts('Q10 SWA'),
            'required' => TRUE,
          ],
          'sum_10swd_728' => [
            'title' => ts('Q10 SWD'),
            'required' => TRUE,
          ],
          'sum_10d_729' => [
            'title' => ts('Q10 D'),
            'required' => TRUE,
          ],
          'sum_10sd_730' => [
            'title' => ts('Q10 SD'),
            'required' => TRUE,
          ],
          'eval_sum_10_731' => [
            'title' => ts('Q10 Total'),
            'required' => TRUE,
          ],
              //Q11
          'sum_11sa_732' => [
            'title' => ts('Q11 SA'),
            'required' => TRUE,
          ],
          'sum_11a_733' => [
            'title' => ts('Q11 A'),
            'required' => TRUE,
          ],
          'sum_11swa_734' => [
            'title' => ts('Q11 SWA'),
            'required' => TRUE,
          ],
          'sum_11swd_735' => [
            'title' => ts('Q11 SWD'),
            'required' => TRUE,
          ],
          'sum_11d_736' => [
            'title' => ts('Q11 D'),
            'required' => TRUE,
          ],
          'sum_11sd_737' => [
            'title' => ts('Q11 SD'),
            'required' => TRUE,
          ],
          'eval_sum_11_738' => [
            'title' => ts('Q11 Total'),
            'required' => TRUE,
          ],
              //Q12
          'sum_12sa_739' => [
            'title' => ts('Q12 SA'),
            'required' => TRUE,
          ],
          'sum_12a_740' => [
            'title' => ts('Q12 A'),
            'required' => TRUE,
          ],
          'sum_12swa_741' => [
            'title' => ts('Q12 SWA'),
            'required' => TRUE,
          ],
          'sum_12swd_742' => [
            'title' => ts('Q12 SWD'),
            'required' => TRUE,
          ],
          'sum_12d_743' => [
            'title' => ts('Q12 D'),
            'required' => TRUE,
          ],
          'sum_12sd_744' => [
            'title' => ts('Q12 SD'),
            'required' => TRUE,
          ],
          'eval_sum_12_745' => [
            'title' => ts('Q12 Total'),
            'required' => TRUE,
          ],
              //Q13
          'sum_13sa_746' => [
            'title' => ts('Q13 SA'),
            'required' => TRUE,
          ],
          'sum_13a_750' => [
            'title' => ts('Q13 A'),
            'required' => TRUE,
          ],
          'sum_13swa_747' => [
            'title' => ts('Q13 SWA'),
            'required' => TRUE,
          ],
          'sum_13swd_751' => [
            'title' => ts('Q13 SWD'),
            'required' => TRUE,
          ],
          'sum_13d_748' => [
            'title' => ts('Q13 D'),
            'required' => TRUE,
          ],
          'sum_13sd_749' => [
            'title' => ts('Q13 SD'),
            'required' => TRUE,
          ],
          'eval_sum_13_752' => [
            'title' => ts('Q13 Total'),
            'required' => TRUE,
          ],
              //Q14
          'sum_14sa_753' => [
            'title' => ts('Q14 SA'),
            'required' => TRUE,
          ],
          'sum_14a_754' => [
            'title' => ts('Q14 A'),
            'required' => TRUE,
          ],
          'sum_14swa_755' => [
            'title' => ts('Q14 SWA'),
            'required' => TRUE,
          ],
          'sum_14swd_756' => [
            'title' => ts('Q14 SWD'),
            'required' => TRUE,
          ],
          'sum_14d_757' => [
            'title' => ts('Q14 D'),
            'required' => TRUE,
          ],
          'sum_14sd_758' => [
            'title' => ts('Q14 SD'),
            'required' => TRUE,
          ],
          'eval_sum_14_759' => [
            'title' => ts('Q14 Total'),
            'required' => TRUE,
          ],
              //Q15
          'sum_15sa_760' => [
            'title' => ts('Q15 SA'),
            'required' => TRUE,
          ],
          'sum_15a_761' => [
            'title' => ts('Q15 A'),
            'required' => TRUE,
          ],
          'sum_15swa_762' => [
            'title' => ts('Q15 SWA'),
            'required' => TRUE,
          ],
          'sum_15swd_763' => [
            'title' => ts('Q15 SWD'),
            'required' => TRUE,
          ],
          'sum_15d_764' => [
            'title' => ts('Q15 D'),
            'required' => TRUE,
          ],
          'sum_15sd_765' => [
            'title' => ts('Q15 SD'),
            'required' => TRUE,
          ],
          'eval_sum_15_766' => [
            'title' => ts('Q15 Total'),
            'required' => TRUE,
          ],
              //Q16
          'sum_16sa_767' => [
            'title' => ts('Q16 SA'),
            'required' => TRUE,
          ],
          'sum_16a_768' => [
            'title' => ts('Q16 A'),
            'required' => TRUE,
          ],
          'sum_16swa_769' => [
            'title' => ts('Q16 SWA'),
            'required' => TRUE,
          ],
          'sum_16swd_770' => [
            'title' => ts('Q16 SWD'),
            'required' => TRUE,
          ],
          'sum_16d_771' => [
            'title' => ts('Q16 D'),
            'required' => TRUE,
          ],
          'sum_16sd_772' => [
            'title' => ts('Q16 SD'),
            'required' => TRUE,
          ],
          'eval_sum_16_773' => [
            'title' => ts('Q16 Total'),
            'required' => TRUE,
          ],
              //Q17
          'sum_17sa_774' => [
            'title' => ts('Q17 SA'),
            'required' => TRUE,
          ],
          'sum_17a_775' => [
            'title' => ts('Q17 A'),
            'required' => TRUE,
          ],
          'sum_17swa_776' => [
            'title' => ts('Q17 SWA'),
            'required' => TRUE,
          ],
          'sum_17swd_777' => [
            'title' => ts('Q17 SWD'),
            'required' => TRUE,
          ],
          'sum_17d_778' => [
            'title' => ts('Q17 D'),
            'required' => TRUE,
          ],
          'sum_17sd_779' => [
            'title' => ts('Q17 SD'),
            'required' => TRUE,
          ],
          'eval_sum_17_780' => [
            'title' => ts('Q17 Total'),
            'required' => TRUE,
          ],
              //Q18
          'eval_sum_18_787' => [
            'title' => ts('Q18 Total'),
            'required' => TRUE,
          ],
          'staff_eval_sum_18_1278' => [
            'title' => ts('Q18 Staff Evaluation'),
            'required' => TRUE,
          ],
              //Q19
          'sum_18sa_781' => [
            'title' => ts('Q19 SA'),
            'required' => TRUE,
          ],
          'sum_18a_782' => [
            'title' => ts('Q19 A'),
            'required' => TRUE,
          ],
          'sum_18swa_783' => [
            'title' => ts('Q19 SWA'),
            'required' => TRUE,
          ],
          'sum_18swd_784' => [
            'title' => ts('Q19 SWD'),
            'required' => TRUE,
          ],
          'sum_18d_785' => [
            'title' => ts('Q19 D'),
            'required' => TRUE,
          ],
          'sum_18sd_786' => [
            'title' => ts('Q19 SD'),
            'required' => TRUE,
          ],
          'eval_sum_19_788' => [
            'title' => ts('Q19 Total'),
            'required' => TRUE,
          ],
              //Q20
          'sum_20sa_789' => [
            'title' => ts('Q20 SA'),
            'required' => TRUE,
          ],
          'sum_20a_790' => [
            'title' => ts('Q20 A'),
            'required' => TRUE,
          ],
          'sum_20swa_791' => [
            'title' => ts('Q20 SWA'),
            'required' => TRUE,
          ],
          'sum_20swd_792' => [
            'title' => ts('Q20 SWD'),
            'required' => TRUE,
          ],
          'sum_20d_793' => [
            'title' => ts('Q20 D'),
            'required' => TRUE,
          ],
          'sum_20sd_794' => [
            'title' => ts('Q20 SD'),
            'required' => TRUE,
          ],
          'eval_sum_20_795' => [
            'title' => ts('Q20 Total'),
            'required' => TRUE,
          ],
              //Q21-1
          'sum_21sa_796' => [
            'title' => ts('Q21-1 SA'),
            'required' => TRUE,
          ],
          'sum_21a_797' => [
            'title' => ts('Q21-1 A'),
            'required' => TRUE,
          ],
          'sum_21swa_798' => [
            'title' => ts('Q21-1 SWA'),
            'required' => TRUE,
          ],
          'sum_21swd_799' => [
            'title' => ts('Q21-1 SWD'),
            'required' => TRUE,
          ],
          'sum_21d_800' => [
            'title' => ts('Q21-1 D'),
            'required' => TRUE,
          ],
          'sum_21sd_801' => [
            'title' => ts('Q21-1 SD'),
            'required' => TRUE,
          ],
          'eval_sum_21_802' => [
            'title' => ts('Q21-1 Total'),
            'required' => TRUE,
          ],
              //Q21-2
          'sum_25sa_1268' => [
            'title' => ts('Q21-2 SA'),
            'required' => TRUE,
          ],
          'sum_25a_1269' => [
            'title' => ts('Q21-2 A'),
            'required' => TRUE,
          ],
          'sum_25swa_1270' => [
            'title' => ts('Q21-2 SWA'),
            'required' => TRUE,
          ],
          'sum_25swd_1271' => [
            'title' => ts('Q21-2 SWD'),
            'required' => TRUE,
          ],
          'sum_25d_1272' => [
            'title' => ts('Q21-2 D'),
            'required' => TRUE,
          ],
          'sum_25sd_1273' => [
            'title' => ts('Q21-2 SD'),
            'required' => TRUE,
          ],
          'eval_sum_25_1274' => [
            'title' => ts('Q21-2 Total'),
            'required' => TRUE,
          ],
              //Q22
          'eval_sum_22_803' => [
            'title' => ts('Q22 Total'),
            'required' => TRUE,
          ],
          'staff_eval_sum_22_1279' => [
            'title' => ts('Q22 Staff Evaluation'),
            'required' => TRUE,
          ],
              //Q23
          'eval_sum_23_804' => [
            'title' => ts('Q18 Total'),
            'required' => TRUE,
          ],
          'staff_eval_sum_23_1280' => [
            'title' => ts('Q18 Staff Evaluation'),
            'required' => TRUE,
          ],
              //Q24
          'sum_24sa_805' => [
            'title' => ts('Q24 SA'),
            'required' => TRUE,
          ],
          'sum_24a_806' => [
            'title' => ts('Q24 A'),
            'required' => TRUE,
          ],
          'sum_24swa_807' => [
            'title' => ts('Q24 SWA'),
            'required' => TRUE,
          ],
          'sum_24swd_808' => [
            'title' => ts('Q24 SWD'),
            'required' => TRUE,
          ],
          'sum_24d_809' => [
            'title' => ts('Q24 D'),
            'required' => TRUE,
          ],
          'sum_24sd_810' => [
            'title' => ts('Q24 SD'),
            'required' => TRUE,
          ],
          'eval_sum_24_811' => [
            'title' => ts('Q24 Total'),
            'required' => TRUE,
          ],
        ],
      ],
    ];
    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;
    parent::__construct();
  }

  public function preProcess() {
    $this->assign('reportTitle', E::ts('Public Ed Bookings Report'));
    parent::preProcess();
  }

  public function from() {
    // CRM_Core_Error::debug($this->_aliases);exit;

    $this->_from = NULL;
    $this->_from = "
      FROM civicrm_activity {$this->_aliases['civicrm_activity']}
      LEFT JOIN civicrm_value_booking_infor_2 {$this->_aliases['civicrm_value_booking_infor_2']} ON {$this->_aliases['civicrm_value_booking_infor_2']}.entity_id = {$this->_aliases['civicrm_activity']}.id
      LEFT JOIN civicrm_value_ped_presentat_54 {$this->_aliases['civicrm_value_ped_presentat_54']} ON {$this->_aliases['civicrm_value_ped_presentat_54']}.entity_id = {$this->_aliases['civicrm_activity']}.id
      LEFT JOIN civicrm_value_ped_booking_r_53 {$this->_aliases['civicrm_value_ped_booking_r_53']} ON {$this->_aliases['civicrm_value_ped_booking_r_53']}.booking_reference_id_706 = {$this->_aliases['civicrm_activity']}.id
      LEFT JOIN civicrm_activity_contact assignee ON assignee.activity_id = {$this->_aliases['civicrm_activity']}.id AND assignee.record_type_id = 2
      LEFT JOIN civicrm_activity_contact target ON target.activity_id = {$this->_aliases['civicrm_activity']}.id AND target.record_type_id = 3
      LEFT JOIN civicrm_activity_contact source ON source.activity_id = {$this->_aliases['civicrm_activity']}.id AND source.record_type_id = 1
      LEFT JOIN civicrm_contact ac ON ac.id = assignee.contact_id
      LEFT JOIN civicrm_contact tc ON tc.id = target.contact_id
      LEFT JOIN civicrm_contact sc ON sc.id = source.contact_id
      ";

  }

  public function groupBy() {
    $this->_groupBy = "
    GROUP BY {$this->_aliases['civicrm_activity']}.id";
  }

  /**
   * Add field specific select alterations.
   *
   * @param string $tableName
   * @param string $tableKey
   * @param string $fieldName
   * @param array $field
   *
   * @return string
   */
  public function selectClause(&$tableName, $tableKey, &$fieldName, &$field) {
    return parent::selectClause($tableName, $tableKey, $fieldName, $field);
  }

  /**
   * Add field specific where alterations.
   *
   * This can be overridden in reports for special treatment of a field
   *
   * @param array $field Field specifications
   * @param string $op Query operator (not an exact match to sql)
   * @param mixed $value
   * @param float $min
   * @param float $max
   *
   * @return null|string
   */
  public function whereClause(&$field, $op, $value, $min, $max) {
    return parent::whereClause($field, $op, $value, $min, $max);
  }

  public function where() {
    $this->_where = "
      WHERE activity_type_id IN (55,59,196,199,201,203,204)
    ";

  }

  public function alterDisplay(&$rows) {

    // custom code to alter rows
    $entryFound = FALSE;
    $checkList = [];
    // CRM_Core_Error::debug($rows); exit();
    foreach ($rows as $rowNum => $row) {

      // if (!empty($this->_noRepeats) && $this->_outputMode != 'csv') {
      //   // not repeat contact display names if it matches with the one
      //   // in previous row
      //   $repeatFound = FALSE;
      //   foreach ($row as $colName => $colVal) {
      //     if (CRM_Utils_Array::value($colName, $checkList) &&
      //       is_array($checkList[$colName]) &&
      //       in_array($colVal, $checkList[$colName])
      //     ) {
      //       $rows[$rowNum][$colName] = "";
      //       $repeatFound = TRUE;
      //     }
      //     if (in_array($colName, $this->_noRepeats)) {
      //       $checkList[$colName][] = $colVal;
      //     }
      //   }
      // }

      // if (array_key_exists('civicrm_membership_membership_type_id', $row)) {
      //   if ($value = $row['civicrm_membership_membership_type_id']) {
      //     $rows[$rowNum]['civicrm_membership_membership_type_id'] = CRM_Member_PseudoConstant::membershipType($value, FALSE);
      //   }
      //   $entryFound = TRUE;
      // }

      // if (array_key_exists('civicrm_address_state_province_id', $row)) {
      //   if ($value = $row['civicrm_address_state_province_id']) {
      //     $rows[$rowNum]['civicrm_address_state_province_id'] = CRM_Core_PseudoConstant::stateProvince($value, FALSE);
      //   }
      //   $entryFound = TRUE;
      // }

      // if (array_key_exists('civicrm_address_country_id', $row)) {
      //   if ($value = $row['civicrm_address_country_id']) {
      //     $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country($value, FALSE);
      //   }
      //   $entryFound = TRUE;
      // }

      // if (array_key_exists('civicrm_contact_sort_name', $row) &&
      //   $rows[$rowNum]['civicrm_contact_sort_name'] &&
      //   array_key_exists('civicrm_contact_id', $row)
      // ) {
      //   $url = CRM_Utils_System::url("civicrm/contact/view",
      //     'reset=1&cid=' . $row['civicrm_contact_id'],
      //     $this->_absoluteUrl
      //   );
      //   $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
      //   $rows[$rowNum]['civicrm_contact_sort_name_hover'] = E::ts("View Contact Summary for this Contact.");
      //   $entryFound = TRUE;
      // }

      // if (!$entryFound) {
      //   break;
      // }
    }
  }
}
