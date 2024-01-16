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
      // @todo The class_exists() check can be removed when support for Drupal
      // <10.1 is dropped.
      if (class_exists(StatementExecutionEndEvent::class)) {
        $this->eventDispatcher->addListener(StatementExecutionEndEvent::class, [
          static::class,
          'onStatementExecutionEnd',
        ]);
      }
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
    $context = new SpanContext();
    $context->setOp('db.sql.query');
    $context->setDescription($event->queryString);
    $connectionInfo = Database::getConnectionInfo($event->key)[$event->target];
    $context->setTags([
      'db.key' => $event->key,
      'db.target' => $event->target,
    ]);
    $data = [
      'db.system' => $connectionInfo['driver'],
      'db.name' => $connectionInfo['database'],
    ];
    if (isset($event->caller['file'])) {
      $data['code.filepath'] = $event->caller['file'];
      $data['code.function'] = $event->caller['function'];
      $data['code.lineno'] = $event->caller['line'];
    }
    if (isset($connectionInfo['host'])) {
      $data['server.address'] = $connectionInfo['host'];
    }
    if (isset($connectionInfo['port'])) {
      $data['server.port'] = $connectionInfo['port'];
    }
    $context->setData($data);
    $context->setStartTimestamp($event->startTime);
    $context->setEndTimestamp($event->time);
    $parent->startChild($context);
  }

  /**
   * If database was initialized, create a span for each logged query.
   *
   * @todo This method will be removed when support for Drupal <10.1 is dropped.
   */
  protected function collectDatabaseLog(): void {
    if (!$this->transaction || !$this->container->initialized('database')) {
      return;
    }
    $connections = [];
    $databaseInfo = Database::getAllConnectionInfo();
    foreach ($databaseInfo as $key => $info) {
      try {
        $database = Database::getConnection('default', $key);
        if ($logger = $database->getLogger()) {
          $connections[$key] = $logger->get('raven');
        }
      }
      catch (\Exception $e) {
        // Could not connect.
      }
    }
    foreach ($connections as $key => $queries) {
      foreach ($queries as $query) {
        if (empty($query['start'])) {
          // Older versions of Drupal do not record query start time.
          return;
        }
        $context = new SpanContext();
        $context->setOp('db.sql.query');
        $context->setDescription($query['query']);
        $context->setTags([
          'db.key' => $key,
          'db.target' => $query['target'],
        ]);
        $data = [
          'db.name' => $databaseInfo[$key][$query['target']]['database'],
          'db.system' => $databaseInfo[$key][$query['target']]['driver'],
        ];
        if (isset($query['caller']['file'])) {
          $data['code.filepath'] = $query['caller']['file'];
          $data['code.function'] = $query['caller']['function'];
          $data['code.lineno'] = $query['caller']['line'];
        }
        if (isset($databaseInfo[$key][$query['target']]['host'])) {
          $data['server.address'] = $databaseInfo[$key][$query['target']]['host'];
        }
        if (isset($databaseInfo[$key][$query['target']]['port'])) {
          $data['server.port'] = $databaseInfo[$key][$query['target']]['port'];
        }
        $context->setData($data);
        $context->setStartTimestamp($query['start']);
        $context->setEndTimestamp($query['start'] + $query['time']);
        $this->transaction->startChild($context);
      }
    }
  }

}
