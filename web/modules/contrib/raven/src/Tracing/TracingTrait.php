<?php

namespace Drupal\raven\Tracing;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Event\StatementExecutionEndEvent;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\TransactionContext;

/**
 * Provides helper methods for performance tracing.
 */
trait TracingTrait {

  /**
   * The transaction for this request or command.
   *
   * @var \Sentry\Tracing\Transaction|null
   */
  protected $transaction;

  /**
   * Starts a transaction, and optionally database tracing.
   */
  protected function startTransaction(TransactionContext $transactionContext): void {
    $config = $this->configFactory->get('raven.settings');
    $transactionContext->setStartTimestamp($this->time->getRequestMicroTime());
    $transaction = \Sentry\startTransaction($transactionContext);
    // If this transaction is not sampled, we can stop here.
    if (!$transaction->getSampled()) {
      return;
    }
    $this->transaction = $transaction;
    SentrySdk::getCurrentHub()->setSpan($this->transaction);
    if ($config->get('database_tracing')) {
      foreach (Database::getAllConnectionInfo() as $key => $info) {
        Database::startLog('raven', $key);
      }
      $this->eventDispatcher->addListener(StatementExecutionEndEvent::class, [
        static::class,
        'onStatementExecutionEnd',
      ]);
    }
  }

  /**
   * Create a span for each database statement.
   */
  public static function onStatementExecutionEnd(StatementExecutionEndEvent $event): void {
    $parent = SentrySdk::getCurrentHub()->getSpan();
    if (!$parent) {
      return;
    }
    $data = [];
    if (isset($event->caller['file'])) {
      $data['code.filepath'] = $event->caller['file'];
      $data['code.function'] = $event->caller['function'];
      $data['code.lineno'] = $event->caller['line'];
    }
    if ($databaseInfo = Database::getConnectionInfo($event->key)) {
      $connectionInfo = $databaseInfo[$event->target];
      $data['db.system'] = $connectionInfo['driver'];
      $data['db.name'] = $connectionInfo['database'];
      if (isset($connectionInfo['host'])) {
        $data['server.address'] = $connectionInfo['host'];
      }
      if (isset($connectionInfo['port'])) {
        $data['server.port'] = $connectionInfo['port'];
      }
    }
    $context = SpanContext::make()
      ->setOp('db.sql.query')
      ->setDescription($event->queryString)
      ->setTags([
        'db.key' => $event->key,
        'db.target' => $event->target,
      ])
      ->setData($data)
      ->setStartTimestamp($event->startTime)
      ->setEndTimestamp($event->time);
    $parent->startChild($context);
  }

}
