<?php

namespace Drupal\raven\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\raven\Logger\RavenInterface;
use Drupal\raven\Tracing\TracingTrait;
use Sentry\Tracing\TransactionSource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Initializes Raven logger so Sentry functions can be called.
 */
class RequestSubscriber implements EventSubscriberInterface, TrustedCallbackInterface {

  use TracingTrait;

  /**
   * Constructs the request subscriber.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected RavenInterface $logger,
    protected TimeInterface $time,
    protected EventDispatcherInterface $eventDispatcher,
    protected ?ContainerInterface $container = NULL,
  ) {
  }

  /**
   * Allows cached container to set the container.
   */
  public function setContainer(ContainerInterface $container = NULL): void {
    $this->container = $container;
  }

  /**
   * Starts a transaction if performance tracing is enabled.
   */
  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }
    $config = $this->configFactory->get('raven.settings');
    if (!$this->logger->getClient()) {
      return;
    }
    $request = $event->getRequest();
    $transactionContext = \Sentry\continueTrace($request->headers->get('sentry-trace', ''), $request->headers->get('baggage', ''));
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
   */
  public function onTerminate(TerminateEvent $event): void {
    if (!$this->transaction) {
      return;
    }
    // Clean up the transaction name if we have a route path.
    if (isset($this->container) && $this->container->initialized('current_route_match')) {
      if ($route = $this->container->get('current_route_match')->getRouteObject()) {
        $this->transaction->setName($event->getRequest()->getMethod() . ' ' . $route->getPath());
        $this->transaction->getMetadata()->setSource(TransactionSource::route());
      }
    }
    $config = $this->configFactory->get('raven.settings');
    $statusCode = $event->getResponse()->getStatusCode();
    $this->transaction->setHttpStatus($statusCode);
    if ($statusCode === Response::HTTP_NOT_FOUND && !$config->get('404_tracing')) {
      $this->transaction->setSampled(FALSE);
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
    return ['#markup' => \Sentry\getTraceparent()];
  }

}
