<?php

namespace Drupal\raven\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Event\StatementExecutionEndEvent;
use Drupal\raven\Logger\RavenInterface;
use Drupal\raven\Tracing\TracingTrait;
use Drush\Commands\DrushCommands;
use Drush\Drush;
use Sentry\SentrySdk;
use Sentry\Severity;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides Drush commands for Raven module.
 */
class RavenCommands extends DrushCommands {

  use TracingTrait;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $drushEventDispatcher;

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The @logger.raven service.
   *
   * @var \Drupal\raven\Logger\RavenInterface
   */
  protected $ravenLogger;

  /**
   * Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $configFactory, ContainerInterface $container, EventDispatcherInterface $eventDispatcher, RavenInterface $ravenLogger, TimeInterface $time) {
    $this->configFactory = $configFactory;
    $this->container = $container;
    $this->drushEventDispatcher = Drush::service('eventDispatcher');
    $this->eventDispatcher = $eventDispatcher;
    $this->ravenLogger = $ravenLogger;
    $this->time = $time;
  }

  /**
   * Sets up Drush error handling and performance tracing.
   *
   * @hook pre-command *
   */
  public function preCommand(CommandData $commandData): void {
    if (!$this->ravenLogger->getClient()) {
      return;
    }
    $config = $this->configFactory->get('raven.settings');
    // Add Drush console error event listener.
    if ($config->get('drush_error_handler')) {
      $this->drushEventDispatcher->addListener(ConsoleEvents::ERROR, [
        $this,
        'onConsoleError',
      ]);
    }
    if (!$config->get('drush_tracing')) {
      return;
    }
    $this->drushEventDispatcher->addListener(ConsoleEvents::TERMINATE, [
      $this,
      'onConsoleTerminate',
    ], -222);
    $transactionContext = new TransactionContext();
    $transactionContext->setName('drush ' . $commandData->input()->getArgument('command'));
    $transactionContext->setSource(TransactionSource::task());
    $transactionContext->setOp('drush.command');
    $this->startTransaction($transactionContext);
  }

  /**
   * Console error event listener.
   */
  public function onConsoleError(ConsoleErrorEvent $event): void {
    \Sentry\captureException($event->getError());
  }

  /**
   * Console terminate event listener.
   */
  public function onConsoleTerminate(ConsoleTerminateEvent $event): void {
    if (!$this->transaction) {
      return;
    }
    $config = $this->configFactory->get('raven.settings');
    // @todo This code can be removed when support for Drupal <10.1 is dropped.
    if ($config->get('database_tracing') && !class_exists(StatementExecutionEndEvent::class)) {
      $this->collectDatabaseLog();
    }
    $this->transaction->setTags(['drush.command.exit_code' => (string) $event->getExitCode()]);
    $this->transaction->finish();
  }

  /**
   * Send a test message to Sentry.
   *
   * Because messages are sent to Sentry asynchronously, there is no guarantee
   * that the message was actually delivered successfully.
   *
   * @param string $message
   *   The message text.
   * @param mixed[] $options
   *   An associative array of options.
   *
   * @option level
   *   The message level (debug, info, warning, error, fatal).
   *
   * @command raven:captureMessage
   * @usage drush raven:captureMessage
   *   Send test message to Sentry.
   * @usage drush raven:captureMessage --level=error
   *   Send error message to Sentry.
   * @usage drush raven:captureMessage 'Mic check.'
   *   Send "Mic check" message to Sentry.
   */
  public function captureMessage(string $message = 'Test message from Drush.', array $options = [
    'level' => 'info',
  ]): void {
    // Force invalid configuration to throw an exception.
    if (!$this->ravenLogger->getClient(FALSE, TRUE)) {
      throw new \Exception('Sentry client not available.');
    }
    elseif (!$this->ravenLogger->getClient()->getOptions()->getDsn()) {
      $this->logger()->warning(dt('Sentry client key is not configured. No events will be sent to Sentry.'));
    }

    $severity = new Severity($options['level']);

    $start = microtime(TRUE);

    $id = \Sentry\captureMessage($message, $severity);

    if ($parent = SentrySdk::getCurrentHub()->getSpan()) {
      $span = new SpanContext();
      $span->setOp('sentry.capture');
      $span->setDescription("$severity: $message");
      $span->setStartTimestamp($start);
      $span->setEndTimestamp(microtime(TRUE));
      $parent->startChild($span);
    }

    if (!$id) {
      throw new \Exception('Send failed.');
    }
    $this->logger()->success(dt('Message sent as event %id.', ['%id' => $id]));
  }

}
