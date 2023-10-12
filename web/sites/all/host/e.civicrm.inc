<?php // civicrm auto-settings
define('CIVICRM_DB_CACHE_CLASS', 'Redis');
define('CIVICRM_DB_CACHE_HOST', 'vredis');
define('CIVICRM_DB_CACHE_PORT', 6379);
define('CIVICRM_DB_CACHE_PASSWORD', '');
define('CIVICRM_DB_CACHE_PREFIX', 'vredis-civicirm');
// todo - check for old settings and modify?
// $civicrm_setting['Directory Preferences']['imageUploadDir'] = '[civicrm.files]/persist/contribute/';
if (!empty($_ENV['VSITE_CIVIMAIL_SMTP_DEFAULT'])) {
  global $civicrm_setting; 
  $civicrm_setting['Mailing Preferences']['mailing_backend'] = [ 'outBound_option' => 0, 'smtpServer' => 'mail', 'smtpPort' => 25, 'smtpAuth' => false ];
}
