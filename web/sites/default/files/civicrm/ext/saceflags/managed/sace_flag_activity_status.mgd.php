<?php

return [
  'OptionGroup_activity_status_OptionValue_Deactivated' => [
    'name' => 'OptionGroup_activity_status_OptionValue_Deactivated',
    'entity' => 'OptionValue',
    'cleanup' => 'unused', 
    'update' => 'unmodified', 
    'params' => [
      'version' => 4, 
      'values' => [
        'option_group_id.name' => 'activity_status', 
        'label' => 'Deactivated', 
        'value' => '19', 
        'name' => 'Deactivated', 
        'grouping' => NULL, 
        'filter' => 0, 
        'is_default' => FALSE, 
        'weight' => 19, 
        'description' => NULL, 
        'is_optgroup' => FALSE, 
        'is_reserved' => TRUE, 
        'is_active' => TRUE, 
        'component_id' => NULL, 
        'domain_id' => NULL, 
        'visibility_id' => NULL, 
        'icon' => NULL, 
        'color' => NULL,
      ], 
      'match' => [
        'option_group_id',
        'name',
      ],
    ],
  ],
];
