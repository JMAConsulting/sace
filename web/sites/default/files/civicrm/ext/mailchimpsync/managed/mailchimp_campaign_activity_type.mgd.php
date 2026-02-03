<?php
use CRM_Mailchimpsync_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_Mailchimp_email_Campaign',
    'entity' => 'OptionValue',
    'cleanup' => 'never',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('Mailchimp Campaign Sent'),
        'name' => 'mailchimp_campaign_sent',
        'description' => E::ts('<p>A Mailchimp &quot;Campaign&quot; (email) was sent.</p>'),
        'icon' => 'fa-mailchimp',
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_Mailchimp_Campaign_ID',
    'entity' => 'CustomGroup',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Mailchimp_Campaign_ID',
        'title' => E::ts('Mailchimp Campaign'),
        'extends' => 'Activity',
	'extends_entity_column_value:name' => [
           'mailchimp_campaign_sent', 
        ],
        'style' => 'Inline',
        'collapse_display' => TRUE,
        'help_pre' => '',
        'help_post' => '',
        'weight' => 15,
        'collapse_adv_display' => TRUE,
        'created_date' => '2025-01-16 17:08:53',
        'is_public' => FALSE,
        'icon' => '',
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_Mailchimp_Campaign_ID_CustomField_Mailchimp_Campaign_ID',
    'entity' => 'CustomField',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'Mailchimp_Campaign_ID',
        'name' => 'Mailchimp_Campaign_ID',
        'label' => E::ts('Mailchimp Campaign ID'),
        'html_type' => 'Text',
        'is_searchable' => TRUE,
        'is_view' => TRUE,
        'text_length' => 32,
        'note_columns' => 60,
        'note_rows' => 4,
        'column_name' => 'mailchimp_campaign_id_73',
      ],
      'match' => [
        'name',
      ],
    ],
  ],
];
