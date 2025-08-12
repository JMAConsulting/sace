<?php
return [
  [
    'name' => 'Job_Import_Mailchimp_campaigns',
    'entity' => 'Job',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'last_run' => '2025-01-17 10:26:40',
        'last_run_end' => '2025-01-17 10:26:41',
        'name' => 'Import Mailchimp campaigns',
        'description' => '',
        'api_entity' => 'Mailchimpsync',
        'api_action' => 'Pullcampaigns',
        'parameters' => '',
      ],
      'match' => [
        'name',
      ],
    ],
  ],
];
