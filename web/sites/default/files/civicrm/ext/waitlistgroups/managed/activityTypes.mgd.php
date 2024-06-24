<?php

use CRM_Waitlistgroups_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_Added_to_Waitlist',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('Added to Waitlist'),
        'name' => 'Added to Waitlist',
      ],
      'match' => [
        'option_group_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionValue_Removed_from_Waitlist',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('Removed from Waitlist'),
        'name' => 'Removed from Waitlist',
      ],
      'match' => [
        'option_group_id',
        'name',
      ],
    ],
  ],
];