<?php
use CRM_Publicedbookingsreport_ExtensionUtil as E;

class CRM_Publicedbookingsreport_Utils {

    public static function addFilter(&$filters, $fields) {
        foreach ($fields as $field) {
            $customField = \Civi\Api4\CustomField::get(FALSE)
                ->addSelect('label', 'option_group_id:name')
                ->addWhere('column_name', '=', $field)
                ->execute()->first();
            if(!empty($customField['label']) && !empty($customField['option_group_id:name'])) {
                $filters[$field] = [
                    'title' => $customField['label'],
                    'type' => CRM_Utils_Type::T_STRING,
                    'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                    'options' => CRM_Core_OptionGroup::values($customField['option_group_id:name']),
                ];
            }
        }
    }
}
?>