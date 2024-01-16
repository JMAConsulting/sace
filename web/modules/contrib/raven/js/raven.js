/**
 * @file
 * Configures @sentry/browser with the Sentry DSN and extra options.
 */

((drupalSettings, Sentry) => {
  // Add the browser performance tracing integration.
  drupalSettings.raven.options.integrations.push(
    new Sentry.Integrations.BrowserTracing(),
  );

  // Show report dialog via beforeSend callback, if enabled.
  if (drupalSettings.raven.showReportDialog) {
    drupalSettings.raven.options.beforeSend = (event) => {
      if (event.exception) {
        Sentry.showReportDialog({ eventId: event.event_id });
      }
      return event;
    };
  }

  // Additional Sentry configuration can be applied by modifying
  // drupalSettings.raven.options in custom PHP or JavaScript. Use the latter
  // for Sentry callback functions; library weight can be used to ensure your
  // custom settings are added before this file executes.
  Sentry.init(drupalSettings.raven.options);

  Sentry.setUser({ id: drupalSettings.user.uid });
})(window.drupalSettings, window.Sentry);
