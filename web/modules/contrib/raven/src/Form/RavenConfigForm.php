<?php

namespace Drupal\raven\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Url;
use Sentry\CheckIn;
use Sentry\Profiling\Profile;
use Sentry\SentrySdk;

/**
 * Implements a Raven Config form.
 */
class RavenConfigForm {

  /**
   * Builds Raven config form.
   *
   * @param mixed[] $form
   *   The logging and errors config form.
   */
  public static function buildForm(array &$form): void {
    $config = \Drupal::configFactory()->getEditable('raven.settings');
    $form['#attached']['library'][] = 'raven/admin';
    $form['raven'] = [
      '#type'           => 'details',
      '#title'          => t('Sentry'),
      '#tree'           => TRUE,
      '#open'           => TRUE,
    ];
    $form['raven']['js'] = [
      '#type'           => 'details',
      '#title'          => t('JavaScript'),
      '#open'           => TRUE,
    ];
    $form['raven']['js']['javascript_error_handler'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Enable JavaScript error handler'),
      '#description'    => t('Check to capture JavaScript errors (if user has the <a target="_blank" href=":url">send JavaScript errors to Sentry</a> permission).', [
        ':url' => Url::fromRoute('user.admin_permissions', [], ['fragment' => 'module-raven'])->toString(),
      ]),
      '#default_value'  => $config->get('javascript_error_handler'),
    ];
    $form['raven']['js']['public_dsn'] = [
      '#type'           => 'textfield',
      '#title'          => t('Sentry DSN'),
      '#default_value'  => $config->get('public_dsn'),
      '#description'    => t('Sentry client key for current site. This setting can be overridden with the SENTRY_DSN environment variable.'),
    ];
    $form['raven']['js']['browser_traces_sample_rate'] = [
      '#type'           => 'number',
      '#title'          => t('Browser performance tracing sample rate'),
      '#default_value'  => $config->get('browser_traces_sample_rate'),
      '#description'    => t('If set to 0 or higher, any parent sampled state will be inherited. If set to empty string, performance tracing will be disabled even if parent was sampled.'),
      '#min'            => 0,
      '#max'            => 1,
      '#step'           => 0.000001,
    ];
    $form['raven']['js']['auto_session_tracking'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Enable auto session tracking'),
      '#description'    => t('Check to monitor release health by sending a session event to Sentry for each page load; only active if a release is specified below or via the SENTRY_RELEASE environment variable.'),
      '#default_value'  => $config->get('auto_session_tracking'),
    ];
    $form['raven']['js']['send_client_reports'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Send client reports'),
      '#description'    => t('Send client report (e.g. number of discarded events), if any, when tab is hidden or closed.'),
      '#default_value'  => $config->get('send_client_reports'),
    ];
    $form['raven']['js']['seckit_set_report_uri'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Send security header reports to Sentry'),
      '#description'    => t('Check to send CSP and CT reports to Sentry if Security Kit module is installed. This setting is not used if CSP module is installed, which has its own UI to configure sending CSP reports to Sentry.'),
      '#default_value'  => $config->get('seckit_set_report_uri'),
      '#disabled'       => !\Drupal::moduleHandler()->moduleExists('seckit'),
    ];
    $form['raven']['js']['show_report_dialog'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Show user feedback dialog'),
      '#description'    => t('Check to allow users to submit a report to Sentry when JavaScript exceptions are thrown.'),
      '#default_value'  => $config->get('show_report_dialog'),
    ];
    $form['raven']['js']['error_embed_url'] = [
      '#type'           => 'textfield',
      '#title'          => t('Error embed URL'),
      '#description'    => t('You will need to define this URL if you want to automatically add CSP rules for the user feedback dialog, and it is not served from the DSN hostname (e.g. enter <code>https://sentry.io</code> if you use the hosted sentry.io).'),
      '#default_value'  => $config->get('error_embed_url'),
      '#disabled'       => !\Drupal::moduleHandler()->moduleExists('csp') && !\Drupal::moduleHandler()->moduleExists('seckit'),
    ];
    $form['raven']['js']['tunnel'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Enable error tunneling'),
      '#description'    => t('Tunnel Sentry events through the website. This is useful, for example, to prevent Sentry requests from getting ad-blocked, or for reaching non-public Sentry servers. See more details in <a href=":url" rel="noreferrer">Sentry\'s JavaScript troubleshooting documentation</a>. Note that CSP reports and user feedback reports, if enabled, will not be tunneled. Tunneled requests will use the timeout specified in the PHP section below.', [
        ':url' => 'https://docs.sentry.io/platforms/javascript/troubleshooting/#using-the-tunnel-option',
      ]),
      '#default_value'  => $config->get('tunnel'),
    ];
    $form['raven']['js']['test'] = [
      '#type'           => 'button',
      '#value'          => t('Send JavaScript test message to Sentry'),
      '#disabled'       => TRUE,
    ];

    $form['raven']['php'] = [
      '#type'           => 'details',
      '#title'          => t('PHP'),
      '#open'           => TRUE,
    ];
    $form['raven']['php']['client_key'] = [
      '#type'           => 'textfield',
      '#title'          => t('Sentry DSN'),
      '#default_value'  => $config->get('client_key'),
      '#description'    => t('Sentry client key for current site. This setting can be overridden with the SENTRY_DSN environment variable.'),
    ];
    // "0" is not a valid checkbox option.
    $log_levels = [];
    foreach (RfcLogLevel::getLevels() as $key => $value) {
      $log_levels[$key + 1] = $value;
    }
    $form['raven']['php']['log_levels'] = [
      '#type'           => 'checkboxes',
      '#title'          => t('Log levels'),
      '#default_value'  => $config->get('log_levels'),
      '#description'    => t('Check the log levels that should be captured by Sentry.'),
      '#options'        => $log_levels,
    ];
    $form['raven']['php']['ignored_channels'] = [
      '#type'           => 'textarea',
      '#title'          => t('Ignored channels'),
      '#description'    => t('A list of log channels for which messages will not be sent to Sentry (one channel per line). Commonly-configured log channels include <em>access denied</em> for 403 errors and <em>page not found</em> for 404 errors.'),
      '#default_value'  => implode("\n", $config->get('ignored_channels') ?: []),
    ];
    $form['raven']['php']['fatal_error_handler'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Enable fatal error handler'),
      '#description'    => t('Check to capture fatal PHP errors.'),
      '#default_value'  => $config->get('fatal_error_handler'),
    ];
    $form['raven']['php']['drush_error_handler'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Enable Drush error handler'),
      '#description'    => t('Check to capture errors thrown by Drush commands.'),
      '#default_value'  => $config->get('drush_error_handler'),
    ];
    $form['raven']['php']['stack'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Enable stacktraces'),
      '#default_value'  => $config->get('stack'),
      '#description'    => t('Check to add stacktraces to reports.'),
    ];
    $form['raven']['php']['trace'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Reflection tracing in stacktraces'),
      '#default_value'  => $config->get('trace'),
      '#description'    => t('Check to enable reflection tracing (function calling arguments) in stacktraces. Warning: This setting allows sensitive data to be logged by Sentry! To enable for exception stacktraces, PHP configuration flag <code>zend.exception_ignore_args</code> must be disabled (see also <code>zend.exception_string_param_max_len</code>).'),
    ];
    $form['raven']['php']['send_user_data'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Send user data to Sentry'),
      '#default_value'  => $config->get('send_user_data'),
      '#description'    => t('Check to send user email and username to Sentry with each event. Warning: User data can still be sent to Sentry even when this setting is disabled, for example as part of a log message or request body. Custom code is required to scrub personally-identifying information from events before they are sent.'),
    ];
    $form['raven']['php']['send_request_body'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Send request body to Sentry'),
      '#default_value'  => $config->get('send_request_body'),
      '#description'    => t('Check to send the request body (POST data) to Sentry. Warning: This setting allows sensitive data to be logged by Sentry!'),
    ];
    $form['raven']['php']['modules'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Send list of installed Composer packages to Sentry'),
      '#default_value'  => $config->get('modules'),
      '#description'    => t('Check to send the list of installed Composer packages to Sentry, including the root project.'),
    ];
    $form['raven']['php']['send_monitoring_sensor_status_changes'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Send Monitoring sensor status changes to Sentry'),
      '#description'    => t('Check to send Monitoring sensor status changes to Sentry if Monitoring module is installed.'),
      '#default_value'  => $config->get('send_monitoring_sensor_status_changes'),
      '#disabled'       => !\Drupal::moduleHandler()->moduleExists('monitoring'),
    ];
    $form['raven']['php']['rate_limit'] = [
      '#type'           => 'number',
      '#title'          => t('Rate limit'),
      '#default_value'  => $config->get('rate_limit'),
      '#description'    => t('Maximum log events sent to Sentry per-request or per-execution. To disable the limit, set to zero. You may need to set a limit if you have buggy code which generates a large number of log messages.'),
      '#min'            => 0,
      '#step'           => 1,
    ];
    $form['raven']['php']['timeout'] = [
      '#type'           => 'number',
      '#title'          => t('Timeout'),
      '#default_value'  => $config->get('timeout'),
      '#description'    => t('Connection timeout in seconds.'),
      '#min'            => 0,
      '#step'           => 0.001,
    ];
    $form['raven']['php']['performance'] = [
      '#type'           => 'details',
      '#title'          => t('Performance tracing'),
      '#open'           => TRUE,
    ];
    $form['raven']['php']['performance']['request_tracing'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Request/response performance tracing'),
      '#default_value'  => $config->get('request_tracing'),
      '#description'    => t('Check to enable performance tracing on the server side for each request/response, excluding pages served from the page cache.'),
    ];
    $form['raven']['php']['performance']['drush_tracing'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Drush performance tracing'),
      '#default_value'  => $config->get('drush_tracing'),
      '#description'    => t('Check to enable performance tracing for each drush command.'),
    ];
    $form['raven']['php']['performance']['database_tracing'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Database performance tracing'),
      '#default_value'  => $config->get('database_tracing'),
      '#description'    => t('Check to add database queries to performance tracing.'),
    ];
    $form['raven']['php']['performance']['twig_tracing'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Twig performance tracing'),
      '#default_value'  => $config->get('twig_tracing'),
      '#description'    => t('Check to add Twig templates to performance tracing.'),
    ];
    $form['raven']['php']['performance']['404_tracing'] = [
      '#type'           => 'checkbox',
      '#title'          => t('404 response performance tracing'),
      '#default_value'  => $config->get('404_tracing'),
      '#description'    => t('Check to enable performance tracing for 404 responses.'),
    ];
    $form['raven']['php']['performance']['traces_sample_rate'] = [
      '#type'           => 'number',
      '#title'          => t('Performance tracing sample rate'),
      '#default_value'  => $config->get('traces_sample_rate'),
      '#description'    => t('If set to 0 or higher, any parent sampled state will be inherited. If set to empty string, performance tracing will be disabled even if parent was sampled.'),
      '#min'            => 0,
      '#max'            => 1,
      '#step'           => 0.000001,
    ];
    $form['raven']['php']['cron_monitor_id'] = [
      '#type'           => 'textfield',
      '#title'          => t('Cron monitor slug'),
      '#description'    => t('To enable cron monitoring, add a cron monitor in the Sentry dashboard and specify the monitor slug here.'),
      '#default_value'  => $config->get('cron_monitor_id'),
      '#disabled'       => !class_exists(CheckIn::class),
    ];
    $form['raven']['php']['performance']['profiles_sample_rate'] = [
      '#type'           => 'number',
      '#title'          => t('Profiling sample rate'),
      '#default_value'  => $config->get('profiles_sample_rate'),
      '#description'    => t('To enable profiling, configure the profiling sample rate. This feature requires the Excimer PHP extension.'),
      '#min'            => 0,
      '#max'            => 1,
      '#step'           => 0.000001,
      '#disabled'       => !class_exists(Profile::class) || !extension_loaded('excimer'),
    ];
    $form['raven']['php']['disable_deprecated_alter'] = [
      '#type'           => 'checkbox',
      '#title'          => t('Disable deprecated alter hooks'),
      '#default_value'  => $config->get('disable_deprecated_alter'),
      '#description'    => t('Check to disable the deprecated alter hooks, or uncheck if you have code that implements hook_raven_breadcrumb_alter(), hook_raven_filter_alter(), or hook_raven_options_alter().'),
    ];
    $form['raven']['php']['test'] = [
      '#type'           => 'button',
      '#value'          => t('Send PHP test message to Sentry'),
      '#disabled'       => TRUE,
      '#attributes'     => ['class' => [class_exists(SentrySdk::class) ? 'installed' : 'missing']],
    ];
    $form['raven']['environment'] = [
      '#type'           => 'textfield',
      '#title'          => t('Environment'),
      '#default_value'  => $config->get('environment'),
      '#description'    => t('The environment in which this site is running (leave blank to use kernel.environment parameter). This setting can be overridden with the SENTRY_ENVIRONMENT environment variable.'),
    ];
    $form['raven']['release'] = [
      '#type'           => 'textfield',
      '#title'          => t('Release'),
      '#default_value'  => $config->get('release'),
      '#description'    => t('The release this site is running (could be a version or commit hash). This setting can be overridden with the SENTRY_RELEASE environment variable.'),
    ];
    $form['#submit'][] = 'Drupal\raven\Form\RavenConfigForm::submitForm';
  }

  /**
   * Submits Raven config form.
   *
   * @param mixed[] $form
   *   The logging and errors config form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function submitForm(array &$form, FormStateInterface $form_state): void {
    \Drupal::configFactory()->getEditable('raven.settings')
      ->set('client_key',
        $form_state->getValue(['raven', 'php', 'client_key']))
      ->set('fatal_error_handler',
        $form_state->getValue(['raven', 'php', 'fatal_error_handler']))
      ->set('drush_error_handler',
        $form_state->getValue(['raven', 'php', 'drush_error_handler']))
      ->set('log_levels',
        $form_state->getValue(['raven', 'php', 'log_levels']))
      ->set('stack',
        $form_state->getValue(['raven', 'php', 'stack']))
      ->set('trace',
        $form_state->getValue(['raven', 'php', 'trace']))
      ->set('send_user_data',
        $form_state->getValue(['raven', 'php', 'send_user_data']))
      ->set('send_request_body',
        $form_state->getValue(['raven', 'php', 'send_request_body']))
      ->set('modules',
        $form_state->getValue(['raven', 'php', 'modules']))
      ->set('send_monitoring_sensor_status_changes',
        $form_state->getValue([
          'raven', 'php', 'send_monitoring_sensor_status_changes',
        ]))
      ->set('rate_limit',
        $form_state->getValue(['raven', 'php', 'rate_limit']))
      ->set('timeout',
        $form_state->getValue(['raven', 'php', 'timeout']))
      ->set('request_tracing',
        $form_state->getValue([
          'raven',
          'php',
          'performance',
          'request_tracing',
        ]))
      ->set('drush_tracing',
        $form_state->getValue([
          'raven',
          'php',
          'performance',
          'drush_tracing',
        ]))
      ->set('database_tracing',
        $form_state->getValue([
          'raven',
          'php',
          'performance',
          'database_tracing',
        ]))
      ->set('twig_tracing',
        $form_state->getValue([
          'raven',
          'php',
          'performance',
          'twig_tracing',
        ]))
      ->set('traces_sample_rate',
        $form_state->getValue([
          'raven',
          'php',
          'performance',
          'traces_sample_rate',
        ]))
      ->set('404_tracing',
        $form_state->getValue([
          'raven',
          'php',
          'performance',
          '404_tracing',
        ]))
      ->set('profiles_sample_rate',
        $form_state->getValue([
          'raven',
          'php',
          'performance',
          'profiles_sample_rate',
        ]))
      ->set('ignored_channels',
        static::extractIgnoredChannels($form_state->getValue([
          'raven',
          'php',
          'ignored_channels',
        ])))
      ->set('cron_monitor_id',
        $form_state->getValue(['raven', 'php', 'cron_monitor_id']))
      ->set('disable_deprecated_alter',
        $form_state->getValue([
          'raven',
          'php',
          'disable_deprecated_alter',
        ]))
      ->set('javascript_error_handler',
        $form_state->getValue(['raven', 'js', 'javascript_error_handler']))
      ->set('public_dsn',
        $form_state->getValue(['raven', 'js', 'public_dsn']))
      ->set('browser_traces_sample_rate',
        $form_state->getValue([
          'raven',
          'js',
          'browser_traces_sample_rate',
        ]))
      ->set('auto_session_tracking',
        $form_state->getValue(['raven', 'js', 'auto_session_tracking']))
      ->set('send_client_reports',
        $form_state->getValue(['raven', 'js', 'send_client_reports']))
      ->set('seckit_set_report_uri',
        $form_state->getValue(['raven', 'js', 'seckit_set_report_uri']))
      ->set('show_report_dialog',
        $form_state->getValue(['raven', 'js', 'show_report_dialog']))
      ->set('error_embed_url',
        $form_state->getValue(['raven', 'js', 'error_embed_url']))
      ->set('tunnel',
        $form_state->getValue(['raven', 'js', 'tunnel']))
      ->set('environment',
        $form_state->getValue(['raven', 'environment']))
      ->set('release',
        $form_state->getValue(['raven', 'release']))
      ->save();
  }

  /**
   * Extracts list of ignored channels from string.
   *
   * @return string[]
   *   Array of ignored channels.
   */
  public static function extractIgnoredChannels(string $string): array {
    return array_map('trim', preg_split('/\R/', $string, -1, PREG_SPLIT_NO_EMPTY));
  }

}
