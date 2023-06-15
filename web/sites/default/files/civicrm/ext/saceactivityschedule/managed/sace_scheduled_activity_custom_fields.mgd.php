<?php

use CRM_Saceactivityschedule_ExtensionUtil as E;

return [
  [
    'entity' => 'CustomGroup',
    'update' => 'unmodified',
    'name' => 'sace_external_activities_custom_group',
    'cleanup' => 'unused',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'CE_External_Activities',
        'label' => E::ts('CE - Extenral Activities'),
        'extends' => 'Activity',
        'style' => 'Inline',
        'is_public' => 1,
        'is_active' => 1,
        'is_multiple' => 0,
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'entity' => 'CustomField',
    'update' => 'unmodified',
    'name' => 'sace_external_activities_outputs_custom_field',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'CE_External_Activities',
        'name' => 'Outputs_or_Deliverables_Completed',
        'label' => E::ts('Outputs or Deliverables Completed'),
        'data_type' => 'Memo',
        'html_type' => 'RichTextEditor',
        'is_required' => FALSE,
        'is_searchable' => FALSE,
        'is_search_range' => FALSE,
        'is_active' => TRUE,
        'is_view' => FALSE,
        'note_columns' => 60,
        'note_rows' => 4,
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
  [
    'entity' => 'CustomField',
    'update' => 'unmodified',
    'name' => 'sace_external_activities_building_room_custom_field',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'CE_External_Activities',
        'name' => 'Building_Room_Location_details',
        'label' => E::ts('Building Room/Location Details'),
        'data_type' => 'String',
        'html_type' => 'Text',
        'is_required' => FALSE,
        'is_searchable' => FALSE,
        'is_search_range' => FALSE,
        'is_active' => TRUE,
        'is_view' => FALSE,
        'text_length' => 255,
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
  [
    'entity' => 'CustomField',
    'update' => 'unmodified',
    'name' => 'sace_external_activities_online_meeting_link_custom_field',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'CE_External_Activities',
        'name' => 'Online Meeting Link',
        'label' => E::ts('Online Meeting Link'),
        'data_type' => 'String',
        'html_type' => 'Text',
        'is_required' => FALSE,
        'is_searchable' => FALSE,
        'is_search_range' => FALSE,
        'is_active' => TRUE,
        'is_view' => FALSE,
        'text_length' => 255,
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];