<?php

use CRM_Sacecustomfields_ExtensionUtil as E;

return [
   [
     'name' => 'CustomGroup_CE_External_Activities',
     'entity' => 'CustomGroup',
     'cleanup' => 'unused',
     'update' => 'unmodified',
     'params' => [
       'version' => 4,
       'values' => [
         'name' => 'CE_External_Activities',
         'title' => E::ts('CE - Extenral Activities'),
         'extends' => 'Activity',
         'style' => 'Inline',
         'weight' => 52,
       ],
       'match' => [
         'name',
       ],
     ],
   ],
   [
     'name' => 'CustomGroup_CE_External_Activities_CustomField_Outputs_or_Deliverables_Completed',
     'entity' => 'CustomField',
     'cleanup' => 'unused',
     'update' => 'unmodified',
     'params' => [
       'version' => 4,
       'values' => [
         'custom_group_id.name' => 'CE_External_Activities',
         'name' => 'Outputs_or_Deliverables_Completed',
         'label' => E::ts('Outputs or Deliverables Completed'),
         'data_type' => 'Memo',
         'html_type' => 'RichTextEditor',
         'is_searchable' => TRUE,
         'note_columns' => 60,
         'note_rows' => 4,
         'column_name' => 'outputs_or_deliverables_complete_1265',
       ],
       'match' => [
         'name',
         'custom_group_id',
       ],
     ],
   ],
   [
     'name' => 'CustomGroup_CE_External_Activities_CustomField_Building_Room_Location_details',
     'entity' => 'CustomField',
     'cleanup' => 'unused',
     'update' => 'unmodified',
     'params' => [
       'version' => 4,
       'values' => [
         'custom_group_id.name' => 'CE_External_Activities',
         'name' => 'Building_Room_Location_details',
         'label' => E::ts('Building Room/Location Details'),
         'html_type' => 'Text',
         'is_searchable' => TRUE,
         'text_length' => 255,
         'column_name' => 'building_room_location_details_1266',
       ],
       'match' => [
         'name',
         'custom_group_id',
       ],
     ],
   ],
   [
     'name' => 'CustomGroup_CE_External_Activities_CustomField_Online_Meeting_Link',
     'entity' => 'CustomField',
     'cleanup' => 'unused',
     'update' => 'unmodified',
     'params' => [
       'version' => 4,
       'values' => [
         'custom_group_id.name' => 'CE_External_Activities',
         'name' => 'Online Meeting Link',
         'label' => E::ts('Online Meeting Link'),
         'html_type' => 'Text',
         'is_searchable' => TRUE,
         'text_length' => 255,
         'column_name' => 'online_meeting_link_1267',
       ],
       'match' => [
         'name',
         'custom_group_id',
       ],
     ],
   ],
   [
     'name' => 'CustomGroup_CE_External_Activities_CustomField_User_Team_filter',
     'entity' => 'CustomField',
     'cleanup' => 'unused',
     'update' => 'unmodified',
     'params' => [
       'version' => 4,
       'values' => [
         'custom_group_id.name' => 'CE_External_Activities',
         'name' => 'User_Team_filter',
         'label' => E::ts('User Team filter'),
         'html_type' => 'Text',
         'is_searchable' => TRUE,
         'text_length' => 512,
         'note_columns' => 60,
         'note_rows' => 4,
         'column_name' => 'user_team_filter_1287',
       ],
       'match' => [
         'name',
         'custom_group_id',
       ],
     ],
   ],
];
