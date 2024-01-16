<?php

namespace Drupal\raven\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Event\StatementExecutionEndEvent;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\raven\Logger\RavenInterface;
use Drupal\raven\Tracing\TracingTrait;
use Sentry\SentrySdk;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Initializes Raven logger so Sentry functions can be called.
 */
class RequestSubscriber implements EventSubscriberInterface, ContainerAwareInterface, TrustedCallbackInterface {

  use ContainerAwareTrait;
  use TracingTrait;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|null
   */
  protected $configFactory;

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|null
   */
  protected $eventDispatcher;

  /**
   * Raven logger service.
   *
   * @var \Drupal\raven\Logger\RavenInterface|null
   */
  protected $logger;

  /**
   * Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs the request subscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory object.
   * @param \Drupal\raven\Logger\RavenInterface $logger
   *   The logger service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   */
  public function __construct(ConfigFactoryInterface $config_factory = NULL, RavenInterface $logger = NULL, TimeInterface $time = NULL, EventDispatcherInterface $event_dispatcher = NULL) {
    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->time = $time;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Starts a transaction if performance tracing is enabled.
   *
   * @todo In Drupal 9+ the event should actually be RequestEvent.
   */
  public function onRequest(KernelEvent $event): void {
    $method = method_exists($event, 'isMainRequest') ? 'isMainRequest' : 'isMasterRequest';
    if (!$this->configFactory || !$event->$method()) {
      return;
    }
    $config = $this->configFactory->get('raven.settings');
    if (!$this->logger || !$this->logger->getClient()) {
      return;
    }
    $request = $event->getRequest();
    $transactionContext = function_exists('Sentry\continueTrace') ? \Sentry\continueTrace($request->headers->get('sentry-trace', ''), $request->headers->get('baggage', '')) : TransactionContext::fromHeaders($request->headers->get('sentry-trace', ''), $request->headers->get('baggage', ''));
    if (!$config->get('request_tracing')) {
      return;
    }
    // This name will later be replaced with the route path, if possible.
    $transactionContext->setName($request->getMethod() . ' ' . $request->getUri());
    $transactionContext->setSource(TransactionSource::url());
    $transactionContext->setOp('http.server');
    $transactionContext->setData([
      'http.request.method' => $request->getMethod(),
      'http.url' => $request->getUri(),
    ]);
    $this->startTransaction($transactionContext);
  }

  /**
   * Performance tracing.
   *
   * @todo In Drupal 9+ the event should actually be TerminateEvent.
   */
  public function onTerminate(KernelEvent $event): void {
    if (!$this->transaction) {
      return;
    }
    // Clean up the transaction name if we have a route path.
    if ($this->container->initialized('current_route_match')) {
      if ($route = $this->container->get('current_route_match')->getRouteObject()) {
        $this->transaction->setName($event->getRequest()->getMethod() . ' ' . $route->getPath());
        $this->transaction->getMetadata()->setSource(TransactionSource::route());
      }
    }
    $config = $this->configFactory->get('raven.settings');
    if (method_exists($event, 'getResponse')) {
      $statusCode = $event->getResponse()->getStatusCode();
      $this->transaction->setHttpStatus($statusCode);
      if ($statusCode === Response::HTTP_NOT_FOUND && !$config->get('404_tracing')) {
        $this->transaction->setSampled(FALSE);
      }
    }

    // @todo This code can be removed when support for Drupal <10.1 is dropped.
    if ($config->get('database_tracing') && !class_exists(StatementExecutionEndEvent::class)) {
      $this->collectDatabaseLog();
    }

    $this->transaction->finish();
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return mixed[]
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 222];
    $events[KernelEvents::TERMINATE][] = ['onTerminate', 222];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['getTraceparent'];
  }

  /**
   * Callback for returning the Sentry trace string as renderable array.
   *
   * @return mixed[]
   *   Renderable array.
   */
  public function getTraceparent(): array {
    $markup = '';
    if (function_exists('Sentry\getTraceparent')) {
      $markup = \Sentry\getTraceparent();
    }
    elseif (class_exists(SentrySdk::class)) {
      if ($span = SentrySdk::getCurrentHub()->getSpan()) {
        $markup = $span->toTraceparent();
      }
    }
    return ['#markup' => $markup];
  }

}
