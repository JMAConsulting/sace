<?php

/**
 * @file
 * Hooks provided by the Raven module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter or suppress breadcrumbs before they are recorded.
 *
 * This hook can be used to alter breadcrumbs before they are recorded. The
 * breadcrumb can be ignored by setting $breadcrumb['process'] to FALSE.
 *
 * @param array $breadcrumb
 *   The parameters passed to the \Drupal\raven\Logger::log() method, as well as
 *   the parameters that will be passed to the \Sentry\addBreadcrumb() function:
 *   - level: The log level passed to \Drupal\raven\Logger::log().
 *   - message: The log message passed to \Drupal\raven\Logger::log().
 *   - context: The original context array passed to Logger::log().
 *   - breadcrumb: Reference to the array to be passed to
 *     \Sentry\addBreadcrumb().
 *   - process: If FALSE, the breadcrumb will not be recorded.
 *
 * @see \Drupal\raven\Logger\Raven::log()
 *
 * @deprecated in raven:4.0.15 and is removed from raven:5.0.0. To alter
 * breadcrumbs, add a before_breadcrumb callback to your client options (options
 * can be configured by subscribing to the \Drupal\raven\Event\OptionsAlter
 * event).
 *
 * @see https://www.drupal.org/project/raven/issues/3354760
 */
function hook_raven_breadcrumb_alter(array &$breadcrumb) {
  // Don't record any breadcrumbs.
  $breadcrumb['process'] = FALSE;
}

/**
 * Alter or suppress log messages before they are sent to Sentry.
 *
 * This hook can be used to alter the captured data before sending to Sentry.
 * The message can be ignored by setting $filter['process'] to FALSE. The Sentry
 * event can be modified by calling \Sentry\Event methods on $filter['event'],
 * e.g. $filter['event']->setTags(), $filter['event']->setContext(), etc.
 *
 * @param array $filter
 *   The parameters passed to the \Drupal\raven\Logger::log() method, as well as
 *   the \Sentry\Event that will be passed to the \Sentry\captureEvent() method:
 *   - level: The log level passed to \Drupal\raven\Logger::log().
 *   - message: The log message passed to \Drupal\raven\Logger::log().
 *   - context: The original context array passed to Logger::log().
 *   - event: The \Sentry\Event object to be passed to \Sentry\captureEvent().
 *   - client: The \Sentry\Client object.
 *   - process: If FALSE, the message will not be sent to Sentry.
 *
 * @see \Drupal\raven\Logger\Raven::log()
 *
 * @deprecated in raven:4.0.15 and is removed from raven:5.0.0. To filter log
 * messages, add a before_send callback to your client options (options can be
 * configured by subscribing to the \Drupal\raven\Event\OptionsAlter event), or
 * add a custom event processor.
 *
 * @see https://www.drupal.org/project/raven/issues/3354760
 */
function hook_raven_filter_alter(array &$filter) {
  // Ignore Sentry logging for certain Rest notices.
  $ignore['rest'] = [
    'Created entity %type with ID %id.',
    'Updated entity %type with ID %id.',
    'Deleted entity %type with ID %id.',
  ];
  foreach ($ignore as $channel => $messages) {
    if ($filter['context']['channel'] === $channel && in_array($filter['message'], $messages)) {
      $filter['process'] = FALSE;
    }
  }
  // Ignore 404 error for certain paths.
  if ($filter['context']['channel'] === 'page not found' && isset($filter['context']['@uri'])) {
    if (in_array($filter['context']['@uri'], ['/wp-login.php'])) {
      $filter['process'] = FALSE;
    }
  }
  // Tag each event with X-Request-ID HTTP header, if present.
  $tags = $filter['event']->getTags();
  $tags['request_id'] = $_SERVER['HTTP_X_REQUEST_ID'] ?? NULL;
  $filter['event']->setTags($tags);
}

/**
 * Alter Sentry PHP client options.
 *
 * @param array $options
 *   The options to be passed to \Sentry\init().
 *
 * @see \Drupal\raven\Logger\Raven::log()
 *
 * @deprecated in raven:4.0.15 and is removed from raven:5.0.0. To customize
 * client options, subscribe to the \Drupal\raven\Event\OptionsAlter event and
 * modify the options property.
 *
 * @see https://www.drupal.org/project/raven/issues/3354760
 */
function hook_raven_options_alter(array &$options) {
  $options['environment'] = getenv('SENTRY_CURRENT_ENV');
}

/**
 * @} End of "addtogroup hooks".
 */
