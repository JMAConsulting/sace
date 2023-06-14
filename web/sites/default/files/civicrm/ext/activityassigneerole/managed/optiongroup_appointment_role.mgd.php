<?
return [
  [
    'name' => 'OptionGroup_appointment_role', 
    'entity' => 'OptionGroup', 
    'cleanup' => 'unused', 
    'update' => 'unmodified', 
    'params' => [
      'version' => 4, 
      'values' => [
        'name' => 'appointment_role', 
        'title' => 'Appointment Role', 
        'description' => NULL, 
        'data_type' => 'String', 
        'is_reserved' => FALSE, 
        'is_active' => TRUE, 
        'is_locked' => FALSE, 
        'option_value_fields' => [
          'name', 
          'label', 
          'description',
        ],
      ], 
      'match' => [
        'name',
      ],
    ],
  ],
];
