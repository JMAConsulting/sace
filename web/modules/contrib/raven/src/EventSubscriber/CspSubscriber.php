<?php

namespace Drupal\raven\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\csp\Csp;
use Drupal\csp\CspEvents;
use Drupal\csp\Event\PolicyAlterEvent;
use Sentry\Dsn;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for CSP events.
 */
class CspSubscriber implements EventSubscriberInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    if (!class_exists(CspEvents::class)) {
      return [];
    }

    $events[CspEvents::POLICY_ALTER] = ['onCspPolicyAlter'];
    return $events;
  }

  /**
   * CspSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Alter CSP policy to allow Sentry to send JS errors.
   *
   * @param \Drupal\csp\Event\PolicyAlterEvent $alterEvent
   *   The Policy Alter event.
   */
  public function onCspPolicyAlter(PolicyAlterEvent $alterEvent): void {
    $policy = $alterEvent->getPolicy();
    $config = $this->configFactory->get('raven.settings');
    if (!$config->get('javascript_error_handler')) {
      return;
    }
    if (!class_exists(Dsn::class)) {
      return;
    }
    $dsn = empty($_SERVER['SENTRY_DSN']) ? $config->get('public_dsn') : $_SERVER['SENTRY_DSN'];
    if (NULL === $dsn) {
      return;
    }
    try {
      $dsn = Dsn::createFromString($dsn);
    }
    catch (\InvalidArgumentException $e) {
      // Raven is incorrectly configured.
      return;
    }
    $connect = [];
    if (!$config->get('tunnel')) {
      $connect[] = $dsn->getStoreApiEndpointUrl();
      $connect[] = $dsn->getEnvelopeApiEndpointUrl();
    }
    if ($config->get('show_report_dialog')) {
      $initial_url = str_replace(
        ["/{$dsn->getProjectId(TRUE)}/", '/store/'],
        ['/embed/', '/error-page/'],
        $dsn->getStoreApiEndpointUrl()
      );
      $script[] = $initial_url;
      if ($final_url = $config->get('error_embed_url')) {
        $connect[] = $script[] = "$final_url/api/embed/error-page/";
      }
      else {
        $connect[] = $initial_url;
      }
      $policy->fallbackAwareAppendIfEnabled('script-src', $script);
      $policy->fallbackAwareAppendIfEnabled('script-src-elem', $script);
      $policy->fallbackAwareAppendIfEnabled('img-src', 'data:');
      $policy->fallbackAwareAppendIfEnabled('style-src', Csp::POLICY_UNSAFE_INLINE);
      $policy->fallbackAwareAppendIfEnabled('style-src-elem', Csp::POLICY_UNSAFE_INLINE);
    }
    $policy->fallbackAwareAppendIfEnabled('connect-src', $connect);
  }

}
