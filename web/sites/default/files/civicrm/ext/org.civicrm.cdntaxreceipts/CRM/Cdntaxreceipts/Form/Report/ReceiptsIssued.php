<?php

use CRM_Cdntaxreceipts_ExtensionUtil as E;

class CRM_Cdntaxreceipts_Form_Report_ReceiptsIssued extends CRM_Report_Form {

  function __construct() {

    $this->_customGroupExtends = ['Contact', 'Individual', 'Organization'];
    $this->_autoIncludeIndexedFieldsAsOrderBys = TRUE;

    $this->_columns = [
      'civicrm_contact' =>
      [
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
        [
          'sort_name' =>
          ['title' => E::ts('Contact Name (Current Value)'),
            'required' => TRUE,
          ],
          'id' =>
          [
            'no_display' => TRUE,
            'required' => TRUE,
            'type' => CRM_Utils_Type::T_INT,
          ],
        ],
        'grouping' => 'tax-fields',
        'order_bys' =>
        [
          'sort_name' =>
          [
            'title' => ts('Last Name, First Name', ['domain' => 'org.civicrm.cdntaxreceipts']),
          ],
        ],
      ],
      'civicrm_address' =>
      [
        'dao' => 'CRM_Core_DAO_Address',
        'fields' =>
        [
          'street_address' =>
          ['default' => FALSE],
          'supplemental_address_1' =>
          ['default' => FALSE],
          'city' =>
          ['default' => FALSE],
          'postal_code' =>
          ['default' => FALSE],
          'state_province_id' =>
          ['title' => E::ts('State/Province'),
            'default' => FALSE],
        ],
      ],
      'civicrm_address_billing' => [
        'dao' => 'CRM_Core_DAO_Address',
        'fields' => [
          'billing_street_address' => [
            'title' => E::ts('Billing Street Address'),
            'default' => FALSE,
            'dbAlias' => 'address_billing_civireport.street_address',
          ],
          'billing_supplemental_address_1' => [
            'title' => E::ts('Billing Supplemental Address 1'),
            'default' => FALSE,
            'dbAlias' => 'address_billing_civireport.supplemental_address_1',
          ],
          'billing_city' => [
            'title' => E::ts('Billing City'),
            'default' => FALSE,
            'dbAlias' => 'address_billing_civireport.city',
          ],
          'billing_postal_code' => [
            'title' => E::ts('Billing Postal Code'),
            'default' => FALSE,
            'dbAlias' => 'address_billing_civireport.postal_code',
          ],
          'billing_state_province_id' => [
            'title' => E::ts('Billing State/Province'),
            'default' => FALSE,
            'dbAlias' => 'address_billing_civireport.state_province_id',
          ],
        ],
      ],
      'civicrm_cdntaxreceipts_log' =>
      [
        'dao' => 'CRM_Contribute_DAO_Contribution',
        'fields' =>
        [
          'issued_on' => ['title' => 'Issued On', 'default' => TRUE,'type' => CRM_Utils_Type::T_TIMESTAMP,],
          'location_issued' => ['title' => 'Location Issued', 'default' => FALSE,],
          'receipt_amount' => ['title' => 'Receipt Amount', 'default' => TRUE, 'type' => CRM_Utils_Type::T_MONEY,],
          'receipt_no' => ['title' => 'Receipt No.', 'default' => TRUE],
          'issue_type' => ['title' => 'Issue Type', 'default' => TRUE],
          'issue_method' => ['title' => 'Issue Method', 'default' => TRUE],
          'uid' => ['title' => 'Issued By', 'default' => TRUE, 'type' => CRM_Utils_Type::T_INT],
          'receipt_status' => ['title' => 'Receipt Status', 'default' => TRUE,],
          'email_opened' => ['title' => 'Email Open Date', 'type' => CRM_Utils_Type::T_TIMESTAMP, 'default' => TRUE],
        ],
        'grouping' => 'tax-fields',
        'filters' =>
        [
          'issued_on' =>
          [
            'title' => 'Issued On',
            'type' => CRM_Utils_Type::T_TIMESTAMP,
            'operatorType' => CRM_Report_Form::OP_DATE],
          'location_issued' =>
          [
            'title' => 'Location Issued',
            'type' => CRM_Utils_Type::T_STRING,
          ],
          'issue_type' =>
            [
              'title' => ts('Issue Type'),
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => ['single' => ts('Single'), 'annual' => ts('Annual'), 'aggregate' => ts('Aggregate')],
              'type' => CRM_Utils_Type::T_STRING,
            ],
          'issue_method' =>
            [
            'title' => ts('Issue Method', ['domain' => 'org.civicrm.cdntaxreceipts']),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => ['email' => 'Email', 'print' => 'Print'],
            'type' => CRM_Utils_Type::T_STRING,
          ],
          'receipt_status' =>
            [
              'title' => ts('Receipt Status', ['domain' => 'org.civicrm.cdntaxreceipts']),
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => ['issued' => 'Issued', 'cancelled' => 'Cancelled'],
              'type' => CRM_Utils_Type::T_STRING,
            ],
          'email_opened' =>
          ['title' => ts('Email Open Date', ['domain' => 'org.civicrm.cdntaxreceipts']),
            'type' => CRM_Utils_Type::T_DATE,
            'operatorType' => CRM_Report_Form::OP_DATE,
          ],
        ],
        'order_bys' =>
        [
          'issued_on' =>
            [
              'title' => 'Issued On', 'default' => '1', 'default_weight' => '0', 'default_order' => 'DESC',
            ],
          'receipt_no' =>
            [
              'title' => ts('Receipt No.', ['domain' => 'org.civicrm.cdntaxreceipts']),
            ],
          'receipt_amount' =>
            [
              'title' => ts('Receipt Amount', ['domain' => 'org.civicrm.cdntaxreceipts']),
            ],
          'receipt_status' =>
            [
              'title' => ts('Receipt Status', ['domain' => 'org.civicrm.cdntaxreceipts']),
            ],
        ],
      ],
      'civicrm_cdntaxreceipts_log_contributions' =>
      [
        'dao' => 'CRM_Contribute_DAO_Contribution',
        'fields' =>
        [
          'contribution_id' => [
            'default' => TRUE,
            'dbAlias' => "GROUP_CONCAT(DISTINCT cdntaxreceipts_log_contributions_civireport.contribution_id ORDER BY cdntaxreceipts_log_contributions_civireport.contribution_id SEPARATOR ', ')",
            'type' => CRM_Utils_Type::T_INT,
           ],
        ],
        'grouping' => 'tax-fields',
      ],
      'civicrm_line_item' => [
        'dao' => 'CRM_Price_DAO_LineItem',
        'fields' => [
          'financial_type_id' => [
            'title' => E::ts('Financial Type (current value)'),
            'default' => FALSE,
            'type' => CRM_Utils_Type::T_STRING,
            // look up words in alterDisplay
            'dbAlias' => "GROUP_CONCAT(DISTINCT line_item_civireport.financial_type_id ORDER BY line_item_civireport.contribution_id, line_item_civireport.financial_type_id SEPARATOR ',')",
          ],
        ],
        'filters' => [],
        'grouping' => 'tax-fields',
      ],
      'civicrm_contribution' => [
        'dao' => 'CRM_Contribute_DAO_Contribution',
        'fields' => [
          'payment_instrument_id' => [
            'title' => E::ts('Payment Method (current value)'),
            'default' => FALSE,
            'type' => CRM_Utils_Type::T_STRING,
            // look up words in alterDisplay
            'dbAlias' => "GROUP_CONCAT(DISTINCT contribution_civireport.payment_instrument_id ORDER BY contribution_civireport.id, contribution_civireport.payment_instrument_id SEPARATOR ',')",
          ],
        ],
        'filters' => [
          /* The problem with this is you then need to join on this table
           * in the statistics section and it messes up the grouping because
           * it's only expecting one table involved.
           *
          'payment_instrument_id' => array(
            'title' => E::ts('Payment Method (current value)'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Contribute_BAO_Contribution::buildOptions('payment_instrument_id', 'get'),
            'type' => CRM_Utils_Type::T_INT,
          ),
           */
        ],
        'grouping' => 'tax-fields',
      ],
    ];

    parent::__construct();
  }

  function preProcess() {
    parent::preProcess();

    //check for permission to edit contributions
    if ( ! CRM_Core_Permission::check('access CiviContribute') ) {
      CRM_Core_Error::fatal(ts('You do not have permission to access this page', ['domain' => 'org.civicrm.cdntaxreceipts']));
    }
  }

  function select() {
    $select = [];
    $this->_columnHeaders = [];
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (!empty($field['required']) ||
            !empty($this->_params['fields'][$fieldName])
          ) {
            $alias = "{$tableName}_{$fieldName}";
            $select[] = "{$field['dbAlias']} as {$alias}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = $field['type'] ?? NULL;
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
            // @todo The right fix is probably in core in Table.tpl
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['group_by'] = NULL;
            $this->_selectAliases[] = $alias;
          }
        }
      }
    }

    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }

  static
  function formRule($fields, $files, $self) {
    $errors = $grouping = [];
    return $errors;
  }

  function from() {
    $this->_from = "
        FROM cdntaxreceipts_log {$this->_aliases['civicrm_cdntaxreceipts_log']}
        INNER JOIN cdntaxreceipts_log_contributions {$this->_aliases['civicrm_cdntaxreceipts_log_contributions']}
                ON {$this->_aliases['civicrm_cdntaxreceipts_log']}.id = {$this->_aliases['civicrm_cdntaxreceipts_log_contributions']}.receipt_id
        LEFT  JOIN civicrm_contact {$this->_aliases['civicrm_contact']}
                ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_cdntaxreceipts_log']}.contact_id
        LEFT  JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']}
                ON {$this->_aliases['civicrm_contribution']}.id = {$this->_aliases['civicrm_cdntaxreceipts_log_contributions']}.contribution_id
        LEFT  JOIN civicrm_line_item {$this->_aliases['civicrm_line_item']}
                ON {$this->_aliases['civicrm_line_item']}.contribution_id = {$this->_aliases['civicrm_cdntaxreceipts_log_contributions']}.contribution_id
        LEFT  JOIN civicrm_address {$this->_aliases['civicrm_address']}
                ON {$this->_aliases['civicrm_address']}.contact_id = {$this->_aliases['civicrm_cdntaxreceipts_log']}.contact_id
               AND {$this->_aliases['civicrm_address']}.is_primary = 1
        LEFT  JOIN civicrm_address {$this->_aliases['civicrm_address_billing']}
		ON {$this->_aliases['civicrm_address_billing']}.contact_id = {$this->_aliases['civicrm_cdntaxreceipts_log']}.contact_id
               AND {$this->_aliases['civicrm_address_billing']}.is_billing = 1";
  }

  function where() {
    $whereClauses = $havingClauses = [];
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('type', $field) & (CRM_Utils_Type::T_DATE | CRM_Utils_Type::T_TIMESTAMP)) {
            if (CRM_Utils_Array::value('operatorType', $field) == CRM_Report_Form::OP_MONTH) {
              $op = $this->_params["{$fieldName}_op"] ?? NULL;
              $value = $this->_params["{$fieldName}_value"] ?? NULL;
              if (is_array($value) && !empty($value)) {
                $clause = "(month({$field['dbAlias']}) $op (" . implode(', ', $value) . '))';
              }
            }
            else {
              $relative = $this->_params["{$fieldName}_relative"] ?? NULL;
              $from     = $this->_params["{$fieldName}_from"] ?? NULL;
              $to       = $this->_params["{$fieldName}_to"] ?? NULL;
              $fromTime = $this->_params["{$fieldName}_from_time"] ?? NULL;
              $toTime   = $this->_params["{$fieldName}_to_time"] ?? NULL;
              $clause   = $this->dateClause($field['dbAlias'], $relative, $from, $to, $field['type'], $fromTime, $toTime);
            }
          }
          else {
            $op = $this->_params["{$fieldName}_op"] ?? NULL;
            if ($op) {
              $clause = $this->whereClause($field,
                $op,
                $this->_params["{$fieldName}_value"] ?? NULL,
                $this->_params["{$fieldName}_min"] ?? NULL,
                $this->_params["{$fieldName}_max"] ?? NULL
              );
            }
          }

          if (!empty($clause)) {
            if (!empty($field['having'])) {
              $havingClauses[] = $clause;
            }
            else {
              $whereClauses[] = $clause;
            }
          }
        }
      }
    }

    if (empty($whereClauses)) {
      $this->_where = "WHERE ( 1 ) ";
      $this->_having = "";
    }
    else {
      $this->_where = "WHERE " . implode(' AND ', $whereClauses);
    }

    if ($this->_aclWhere) {
      $this->_where .= " AND {$this->_aclWhere} ";
    }

    if (!empty($havingClauses)) {
      // use this clause to construct group by clause.
      $this->_having = "HAVING " . implode(' AND ', $havingClauses);
    }
    $this->_where .= " AND {$this->_aliases['civicrm_cdntaxreceipts_log']}.is_duplicate = 0 ";
  }

  function groupBy( ) {
    // required for GROUP_CONCAT
    $this->_groupBy = "GROUP BY {$this->_aliases['civicrm_cdntaxreceipts_log']}.id";
  }

  function postProcess() {

    $this->beginPostProcess();

    $sql = $this->buildQuery(TRUE);

    $rows = $graphRows = [];
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  function alterDisplay(&$rows) {
    // custom code to alter rows
    $entryFound = FALSE;
    $defined_financial_types = CRM_Contribute_BAO_Contribution::buildOptions('financial_type_id', 'get');
    $defined_payment_methods = CRM_Contribute_BAO_Contribution::buildOptions('payment_instrument_id', 'get');

    foreach ($rows as $rowNum => $row) {

      // change contact name with link
      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        require_once('CRM/Utils/System.php');
        $url = CRM_Utils_System::url("civicrm/contact/view",
                  'reset=1&cid=' . $row['civicrm_contact_id'],
                  $this->_absoluteUrl
               );
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("View Contact Summary for this Contact");
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_cdntaxreceipts_log_issue_type', $row)) {
        if ($rows[$rowNum]['civicrm_cdntaxreceipts_log_issue_type'] == 'single' ) {
          $rows[$rowNum]['civicrm_cdntaxreceipts_log_issue_type'] = ts('Single', ['domain' => 'org.civicrm.cdntaxreceipts']);
        }
        elseif ($rows[$rowNum]['civicrm_cdntaxreceipts_log_issue_type'] == 'annual' ) {
          $rows[$rowNum]['civicrm_cdntaxreceipts_log_issue_type'] = ts('Annual', ['domain' => 'org.civicrm.cdntaxreceipts']);
        }
        elseif ($rows[$rowNum]['civicrm_cdntaxreceipts_log_issue_type'] == 'aggregate' ) {
          $rows[$rowNum]['civicrm_cdntaxreceipts_log_issue_type'] = ts('Aggregate', ['domain' => 'org.civicrm.cdntaxreceipts']);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_cdntaxreceipts_log_issue_method', $row)) {
        if ($rows[$rowNum]['civicrm_cdntaxreceipts_log_issue_method'] == 'print' ) {
          $rows[$rowNum]['civicrm_cdntaxreceipts_log_issue_method'] = ts('Print', ['domain' => 'org.civicrm.cdntaxreceipts']);
        }
        elseif ($rows[$rowNum]['civicrm_cdntaxreceipts_log_issue_method'] == 'email' ) {
          $rows[$rowNum]['civicrm_cdntaxreceipts_log_issue_method'] = ts('Email', ['domain' => 'org.civicrm.cdntaxreceipts']);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_cdntaxreceipts_log_receipt_status', $row)) {
        if ($rows[$rowNum]['civicrm_cdntaxreceipts_log_receipt_status'] == 'issued' ) {
          $rows[$rowNum]['civicrm_cdntaxreceipts_log_receipt_status'] = ts('Issued', ['domain' => 'org.civicrm.cdntaxreceipts']);
        }
        elseif ($rows[$rowNum]['civicrm_cdntaxreceipts_log_receipt_status'] == 'cancelled' ) {
          $rows[$rowNum]['civicrm_cdntaxreceipts_log_receipt_status'] = ts('Cancelled', ['domain' => 'org.civicrm.cdntaxreceipts']);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_cdntaxreceipts_log_issued_on', $row)) {
        $rows[$rowNum]['civicrm_cdntaxreceipts_log_issued_on'] = date('Y-m-d', strtotime($rows[$rowNum]['civicrm_cdntaxreceipts_log_issued_on']));
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_cdntaxreceipts_log_uid', $row)) {
        $issued_by = CRM_Core_BAO_UFMatch::getUFValues($rows[$rowNum]['civicrm_cdntaxreceipts_log_uid']);
        if( $issued_by ) {
          $rows[$rowNum]['civicrm_cdntaxreceipts_log_uid'] = $issued_by['uf_name'];
          $entryFound = TRUE;
        }
      }

      if (array_key_exists('civicrm_address_state_province_id', $row)) {
        if ($value = $row['civicrm_address_state_province_id']) {
          $rows[$rowNum]['civicrm_address_state_province_id'] = CRM_Core_PseudoConstant::stateProvinceAbbreviation($value, FALSE);
        }
	$entryFound = TRUE;
      }

      if (array_key_exists('civicrm_address_billing_billing_state_province_id', $row)) {
	if ($value = $row['civicrm_address_billing_billing_state_province_id']) {
          $rows[$rowNum]['civicrm_address_billing_billing_state_province_id'] = CRM_Core_PseudoConstant::stateProvinceAbbreviation($value, FALSE);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_line_item_financial_type_id', $row)) {
        $financial_types = explode(',', $row['civicrm_line_item_financial_type_id'] ?? '');
        $financial_types = array_map(function($t) use ($defined_financial_types) {
          return $defined_financial_types[$t] ?? E::ts('Unknown');
        }, $financial_types);
        $rows[$rowNum]['civicrm_line_item_financial_type_id'] = implode(', ', $financial_types);
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_contribution_payment_instrument_id', $row)) {
        $payment_methods = explode(',', $row['civicrm_contribution_payment_instrument_id'] ?? '');
        $payment_methods = array_map(function($t) use ($defined_payment_methods) {
          return $defined_payment_methods[$t] ?? E::ts('Unknown');
        }, $payment_methods);
        $rows[$rowNum]['civicrm_contribution_payment_instrument_id'] = implode(', ', $payment_methods);
        $entryFound = TRUE;
      }

      // skip looking further in rows, if first row itself doesn't
      // have the column we need
      if (!$entryFound) {
        break;
      }
    }
  }

  function statistics(&$rows) {
    $statistics = parent::statistics($rows);

    $totalAmount = $average = [];
    $count = 0;
    $select = "
        SELECT COUNT({$this->_aliases['civicrm_cdntaxreceipts_log']}.receipt_amount ) as count,
               SUM( {$this->_aliases['civicrm_cdntaxreceipts_log']}.receipt_amount ) as amount,
               ROUND(AVG({$this->_aliases['civicrm_cdntaxreceipts_log']}.receipt_amount), 2) as avg
        ";

    // @todo FIXME
    $where = $this->getRidOfLineItemsAclWhere();
    $sql = "{$select}
      FROM cdntaxreceipts_log {$this->_aliases['civicrm_cdntaxreceipts_log']}
      {$where}";

    $dao = CRM_Core_DAO::executeQuery($sql);

    while ($dao->fetch()) {
      $totalAmount[] = CRM_Utils_Money::format($dao->amount, 'CAD');
      $average[] =   CRM_Utils_Money::format($dao->avg, 'CAD');
      $count += $dao->count;
    }
    $statistics['counts']['amount'] = [
      'title' => ts('Total Amount Issued', ['domain' => 'org.civicrm.cdntaxreceipts']),
      'value' => implode(',  ', $totalAmount),
      'type' => CRM_Utils_Type::T_STRING,
    ];
    $statistics['counts']['count'] = [
      'title' => ts('Number Issued', ['domain' => 'org.civicrm.cdntaxreceipts']),
      'value' => $count,
      'type' => CRM_Utils_Type::T_INT,
    ];
    $statistics['counts']['avg'] = [
      'title' => ts('Average Amount Issued', ['domain' => 'org.civicrm.cdntaxreceipts']),
      'value' => implode(',  ', $average),
      'type' => CRM_Utils_Type::T_STRING,
    ];
    return $statistics;
  }

  /**
   * @todo FIXME Core contains a built-in ACL on line items where it restricts
   * the entity_table, but it messes up our grouping here. So as a quickfix
   * this removes it. In this report we know the line items are always related
   * to contributions, but this is still a bit risky and not the right way to
   * do this.
   * @return string
   */
  private function getRidOfLineItemsAclWhere(): string {
    $where = $this->_where;
    $lineItemsAclWhere = implode(' AND ', CRM_Price_BAO_LineItem::getSelectWhereClause($this->_aliases['civicrm_line_item']));
    if (!empty($lineItemsAclWhere) && strpos($where, "AND $lineItemsAclWhere") !== FALSE) {
      $where = str_replace("AND $lineItemsAclWhere", ' ', $where);
    }
    return $where;
  }

}

