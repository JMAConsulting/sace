#!/bin/bash
# Use my composer to update my civicrm version
echo "Updating CiviCRM Codebase using composer."
cd /var/www/drupal
sudo -u drupal composer require --no-update civicrm/civicrm-core:^5.28 civicrm/civicrm-drupal-8:^5.28 civicrm/civicrm-packages:^5.28 
sudo -u drupal composer update --with-dependencies
sudo -u drupal composer civicrm:publish
cv upgrade:db
chown -R www-data:www-data web/sites/default/files/civicrm
echo 'RewriteEngine Off' > /var/www/drupal/web/libraries/civicrm/core/extern/.htaccess
