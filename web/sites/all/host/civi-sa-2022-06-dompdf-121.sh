#!/bin/bash
cd /var/www/drupal
sudo -u drupal php -d memory_limit=-1 /usr/local/bin/composer require 'dompdf/dompdf:1.2.1 as 1.0.3'
