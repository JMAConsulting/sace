<?php

use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\query\QueryPluginBase;

/**
 * Implements hook_views_query_alter().
 */
function clin_client_profile_views_query_alter(ViewExecutable $view, QueryPluginBase $query) {

  \Drupal::logger('clin_client_profile')->notice('<pre>'.print_r($query, TRUE).'</pre>');
  if ($view->id() == 'clin_flags') {
    $query->leftJoin('staging_sace_civicrm.civicrm_saceflags', 'saceflags', 'sace_activity.id = saceflags.activity_id');
    $query->condition('saceflags.used_for_contact_flagging', TRUE);
  }

}
