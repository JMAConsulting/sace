<?php

namespace Drupal\xhprof\EventSubscriber;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\xhprof\ProfilerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Provides handling of start/stop for profiler.
 */
class XHProfEventSubscriber implements EventSubscriberInterface {

  /**
   * The profiler.
   *
   * @var \Drupal\xhprof\ProfilerInterface
   */
  public $profiler;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * The profiling run ID.
   *
   * @var string
   */
  private $xhprofRunId;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * Constructs XHProfEventSubscriber object.
   *
   * @param \Drupal\xhprof\ProfilerInterface $profiler
   *   The profiler.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ProfilerInterface $profiler, AccountInterface $currentUser, ModuleHandlerInterface $module_handler) {
    $this->profiler = $profiler;
    $this->currentUser = $currentUser;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 0],
      KernelEvents::RESPONSE => ['onKernelResponse', 0],
      KernelEvents::TERMINATE => ['onKernelTerminate', 0],
    ];
  }

  /**
   * Enables profiling if allowed.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event.
   */
  public function onKernelRequest(RequestEvent $event) {
    if ($this->profiler->canEnable($event->getRequest())) {
      $this->profiler->enable();
    }
  }

  /**
   * Renders link to the finished run.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event.
   */
  public function onKernelResponse(ResponseEvent $event) {
    if ($this->profiler->isEnabled()) {
      $this->xhprofRunId = $this->profiler->createRunId();

      // Don't print the link to xhprof run page if
      // Webprofiler module is enabled, a widget will
      // be rendered into Webprofiler toolbar.
      if (!$this->moduleHandler->moduleExists('webprofiler')) {
        $response = $event->getResponse();

        // Try not to break non html pages.
        $formats = [
          'xml',
          'javascript',
          'json',
          'plain',
          'image',
          'application',
          'csv',
          'x-comma-separated-values',
        ];
        foreach ($formats as $format) {
          if ($response->headers->get($format)) {
            return;
          }
        }

        if ($this->currentUser->hasPermission('access xhprof data')) {
          $this->injectLink($response, $this->xhprofRunId);
        }
      }
    }
  }

  /**
   * Stops profiling and saves data.
   */
  public function onKernelTerminate() {
    if ($this->profiler->isEnabled()) {
      $this->profiler->shutdown($this->xhprofRunId);
    }
  }

  /**
   * Adds link to view report to the end of the response.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   The response.
   * @param string $xhprofRunId
   *   The run ID.
   */
  protected function injectLink(Response $response, $xhprofRunId) {
    $content = $response->getContent();
    $pos = mb_strripos($content, '</body>');

    if (FALSE !== $pos) {
      $output = '<div class="xhprof-ui">' . $this->profiler->link($xhprofRunId) . '</div>';
      $content = mb_substr($content, 0, $pos) . $output . mb_substr($content, $pos);
      $response->setContent($content);
    }
  }

}
