<?php

use CRM_ReportError_ExtensionUtil as E;

class CRM_ReportError_Utils {

  /**
   * Sends an email report about the error.
   */
  public static function sendReport($vars, $options_overrides) {
    $domain = CRM_Core_BAO_Domain::getDomain();
    $site_name = $domain->name;

    $len = REPORTERROR_CIVICRM_SUBJECT_LEN;

    if (!empty($vars['reporterror_subject'])) {
      $subject = E::ts('CiviCRM error [%2] at %1', [1 => $site_name, 2 => $vars['reporterror_subject']]);
    }
    else {
      $subject = E::ts('CiviCRM error at %1', [1 => $site_name]);
    }

    if ($len) {
      $subject .= ' (' . substr($vars['message'], 0, $len) . ')';
    }

    $to = reporterror_setting_get('reporterror_mailto', $options_overrides);

    if (!empty($to)) {
      $destinations = explode(REPORTERROR_EMAIL_SEPARATOR, $to);
      $output = reporterror_civicrm_generatereport($site_name, $vars, NULL, $options_overrides);

      foreach ($destinations as $dest) {
        $dest = trim($dest);
        reporterror_civicrm_send_mail($dest, $subject, $output, $options_overrides);
      }
    }
    else {
      Civi::log()->warning('Report Error Extension could not send since no email address was set.');
    }
  }

  /**
   * Generates a 404 HTTP response. Useful for bots who should avoid indexing
   * a given page.
   */
  public static function generate404() {
    $config = CRM_Core_Config::singleton();

    switch ($config->userFramework) {
      case 'Drupal':
      case 'Drupal6':
        drupal_not_found();
        drupal_exit();
        break;

      case 'Drupal8':
        // TODO: not tested.
        // use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        // throw new NotFoundHttpException();
        break;

      case 'WordPress':
        // TODO: not tested.
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        break;

      case 'Joomla':
        // TODO: not tested.
        header("HTTP/1.0 404 Not Found");
        break;

      default:
        header("HTTP/1.0 404 Not Found");
    }
  }

}
