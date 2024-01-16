<?php

namespace Drupal\raven\Logger;

use Drupal\Component\ClassFinder\ClassFinder;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\EventSubscriber\ExceptionLoggingSubscriber;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLoggerTrait;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Drupal\raven\Event\OptionsAlter;
use Drupal\raven\Exception\RateLimitException;
use Drupal\raven\Integration\RemoveExceptionFrameVarsIntegration;
use Drupal\raven\Integration\SanitizeIntegration;
use Drush\Drush;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Sentry\Breadcrumb;
use Sentry\ClientBuilder;
use Sentry\ClientInterface;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\ExceptionMechanism;
use Sentry\Integration\EnvironmentIntegration;
use Sentry\Integration\FatalErrorListenerIntegration;
use Sentry\Integration\FrameContextifierIntegration;
use Sentry\Integration\ModulesIntegration;
use Sentry\Integration\RequestIntegration;
use Sentry\Integration\TransactionIntegration;
use Sentry\Profiling\Profile;
use Sentry\SentrySdk;
use Sentry\Severity;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;
use Sentry\UserDataBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Logs events to Sentry.
 */
class Raven implements LoggerInterface, RavenInterface {

  use DependencySerializationTrait;
  use RfcLoggerTrait;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|null
   */
  protected $currentUser;

  /**
   * Environment.
   *
   * @var string
   */
  protected $environment;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The message's placeholders parser.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $parser;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|null
   */
  protected $requestStack;

  /**
   * The settings array.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Constructs a Raven log object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory object.
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The parser to use when extracting message variables.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param string $environment
   *   The kernel.environment parameter.
   * @param \Drupal\Core\Session\AccountInterface|null $current_user
   *   The current user (optional).
   * @param \Symfony\Component\HttpFoundation\RequestStack|null $request_stack
   *   The request stack (optional).
   * @param \Drupal\Core\Site\Settings|null $settings
   *   The settings array (optional).
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher (optional for BC with cached container).
   */
  public function __construct(ConfigFactoryInterface $config_factory, LogMessageParserInterface $parser, ModuleHandlerInterface $module_handler, $environment, AccountInterface $current_user = NULL, RequestStack $request_stack = NULL, Settings $settings = NULL, EventDispatcherInterface $event_dispatcher = NULL) {
    $this->configFactory = $config_factory;
    $config = $this->configFactory->get('raven.settings');
    $this->currentUser = $current_user;
    $this->requestStack = $request_stack;
    $this->moduleHandler = $module_handler;
    $this->parser = $parser;
    $this->environment = $config->get('environment') ?: $environment;
    $this->settings = $settings ?: Settings::getInstance();
    $this->eventDispatcher = $event_dispatcher;
    // We cannot lazily initialize Sentry, because we want the scope to be
    // immediately available for adding context, etc.
    $this->getClient();
  }

  /**
   * {@inheritdoc}
   */
  public function getClient(bool $force_new = FALSE, bool $force_throw = FALSE): ?ClientInterface {
    // Gracefully handle missing or wrong version of Sentry SDK.
    if (!class_exists(SentrySdk::class) || !method_exists(Event::class, 'createEvent')) {
      return NULL;
    }
    // Is the client already initialized?
    if (!$force_new && ($client = SentrySdk::getCurrentHub()->getClient())) {
      return $client;
    }
    $config = $this->configFactory->get('raven.settings');
    $options = [
      'default_integrations' => FALSE,
      'dsn' => empty($_SERVER['SENTRY_DSN']) ? $config->get('client_key') : $_SERVER['SENTRY_DSN'],
      'environment' => empty($_SERVER['SENTRY_ENVIRONMENT']) ? $this->environment : $_SERVER['SENTRY_ENVIRONMENT'],
      'send_attempts' => 0,
    ];
    if (!is_null($timeout = $config->get('timeout'))) {
      $options['http_connect_timeout'] = $options['http_timeout'] = $timeout;
    }
    if ($config->get('stack')) {
      $options['attach_stacktrace'] = TRUE;
    }
    if ($config->get('fatal_error_handler')) {
      $options['integrations'][] = new FatalErrorListenerIntegration();
    }
    $options['integrations'][] = new RequestIntegration();
    $options['integrations'][] = new TransactionIntegration();
    $options['integrations'][] = new FrameContextifierIntegration();
    $options['integrations'][] = new EnvironmentIntegration();
    $options['integrations'][] = new SanitizeIntegration();
    if (!$config->get('trace')) {
      $options['integrations'][] = new RemoveExceptionFrameVarsIntegration();
    }
    if ($config->get('modules')) {
      $options['integrations'][] = new ModulesIntegration();
    }

    if (!empty($_SERVER['SENTRY_RELEASE'])) {
      $options['release'] = $_SERVER['SENTRY_RELEASE'];
    }
    elseif (!empty($config->get('release'))) {
      $options['release'] = $config->get('release');
    }
    if (!$config->get('send_request_body')) {
      // @todo Should be 'never' when support for SDK 3.9 is dropped.
      $options['max_request_body_size'] = 'none';
    }
    if (!is_null($traces = $config->get('traces_sample_rate'))) {
      $options['traces_sample_rate'] = $traces;
    }
    if (class_exists(Profile::class)) {
      $options['profiles_sample_rate'] = $config->get('profiles_sample_rate');
    }

    // Proxy configuration (DSN is null before install).
    $parsed_dsn = parse_url($options['dsn'] ?? '');
    if (!empty($parsed_dsn['host']) && !empty($parsed_dsn['scheme'])) {
      $http_client_config = $this->settings->get('http_client_config', []);
      if (!empty($http_client_config['proxy'][$parsed_dsn['scheme']])) {
        $no_proxy = $http_client_config['proxy']['no'] ?? [];
        // No need to configure proxy if Sentry host is on proxy bypass list.
        if (!in_array($parsed_dsn['host'], $no_proxy, TRUE)) {
          $options['http_proxy'] = $http_client_config['proxy'][$parsed_dsn['scheme']];
        }
      }
    }

    if (!$config->get('disable_deprecated_alter')) {
      $this->moduleHandler->alter('raven_options', $options);
    }
    // @phpstan-ignore-next-line Cached container BC shim.
    if ($this->eventDispatcher) {
      $this->eventDispatcher->dispatch(new OptionsAlter($options), OptionsAlter::class);
    }
    try {
      // If we're in Drush debug mode, attach Drush logger to Sentry client.
      if (function_exists('drush_main') && Drush::debug()) {
        SentrySdk::init()->bindClient(ClientBuilder::create($options)->setLogger(Drush::logger())->getClient());
      }
      else {
        \Sentry\init($options);
      }
    }
    catch (\InvalidArgumentException $e) {
      if ($force_throw) {
        throw $e;
      }
      return NULL;
    }
    // Set default user context.
    \Sentry\configureScope(function (Scope $scope) use ($config): void {
      $user = ['id' => $this->currentUser ? $this->currentUser->id() : 0];
      if ($this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
        $user['ip_address'] = $request->getClientIp();
      }
      if ($this->currentUser && $config->get('send_user_data')) {
        $user['email'] = $this->currentUser->getEmail();
        $user['username'] = $this->currentUser->getAccountName();
      }
      $scope->setUser($user);
    });
    return SentrySdk::getCurrentHub()->getClient();
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []): void {
    static $counter = 0;
    $client = $this->getClient();
    if (!$client) {
      return;
    }
    $config = $this->configFactory->get('raven.settings');
    $event = Event::createEvent();
    $levels = [
      RfcLogLevel::EMERGENCY => Severity::FATAL,
      RfcLogLevel::ALERT => Severity::FATAL,
      RfcLogLevel::CRITICAL => Severity::FATAL,
      RfcLogLevel::ERROR => Severity::ERROR,
      RfcLogLevel::WARNING => Severity::WARNING,
      RfcLogLevel::NOTICE => Severity::INFO,
      RfcLogLevel::INFO => Severity::INFO,
      RfcLogLevel::DEBUG => Severity::DEBUG,
    ];
    $event->setLevel(new Severity($levels[$level] ?? Severity::INFO));

    // Remove backtrace string from the message, as it is redundant with Sentry
    // stack traces, and could leak function calling arguments to Sentry
    // (depending on the configuration of zend.exception_ignore_args and
    // zend.exception_string_param_max_len).
    if (isset($context['@backtrace_string'])) {
      $message = str_replace(' @backtrace_string', '', $message);
      unset($context['@backtrace_string']);
    }
    $message_placeholders = $this->parser->parseMessagePlaceholders($message, $context);
    $formatted_message = empty($message_placeholders) ? $message : strtr($message, $message_placeholders);
    $event->setMessage($message, $message_placeholders, $formatted_message);
    $event->setTimestamp($context['timestamp']);
    $event->setLogger($context['channel']);
    $extra = ['request_uri' => $context['request_uri']];
    if ($context['referer']) {
      $extra['referer'] = $context['referer'];
    }
    if ($context['link']) {
      $extra['link'] = MailFormatHelper::htmlToText($context['link']);
    }
    $event->setExtra($extra);
    $user = UserDataBag::createFromUserIdentifier($context['uid']);
    $user->setIpAddress($context['ip'] ?: NULL);
    if ($this->currentUser && $this->currentUser->id() == $context['uid'] && $config->get('send_user_data')) {
      $user->setEmail($this->currentUser->getEmail());
      $user->setUsername($this->currentUser->getAccountName());
    }
    $event->setUser($user);
    if ($client->getOptions()->shouldAttachStacktrace()) {
      if (isset($context['backtrace'])) {
        $backtrace = $context['backtrace'];
        if (!$config->get('trace')) {
          foreach ($backtrace as &$frame) {
            unset($frame['args']);
          }
        }
      }
      else {
        $backtrace = debug_backtrace($config->get('trace') ? 0 : DEBUG_BACKTRACE_IGNORE_ARGS);
        // Remove any logger stack frames.
        $finder = new ClassFinder();
        $class_file = $finder->findFile(LoggerChannel::class);
        if ($class_file && isset($backtrace[0]['file']) && $backtrace[0]['file'] === realpath($class_file)) {
          array_shift($backtrace);
          $class_file = $finder->findFile(LoggerTrait::class);
          if ($class_file && isset($backtrace[0]['file']) && $backtrace[0]['file'] === realpath($class_file)) {
            array_shift($backtrace);
          }
        }
      }
      $stacktrace = $client->getStacktraceBuilder()->buildFromBacktrace($backtrace, '', 0);
      $stacktrace->removeFrame(count($stacktrace->getFrames()) - 1);
      $event->setStacktrace($stacktrace);
    }

    // Allow modules to alter or ignore this message.
    $filter = [
      'level' => $level,
      'message' => $message,
      'context' => $context,
      'event' => $event,
      'client' => $client,
      'process' => !empty($config->get('log_levels')[$level + 1]),
    ];
    if (in_array($context['channel'], $config->get('ignored_channels') ?: [])) {
      $filter['process'] = FALSE;
    }
    if (!$config->get('disable_deprecated_alter')) {
      $this->moduleHandler->alter('raven_filter', $filter);
    }
    if (!empty($filter['process'])) {
      $eventHint['extra'] = [
        'level' => $level,
        'message' => $message,
        'context' => $context,
      ];
      if (isset($stacktrace)) {
        $eventHint['stacktrace'] = $stacktrace;
      }
      if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
        $eventHint['exception'] = $context['exception'];
        // Capture "critical" uncaught exceptions logged by
        // ExceptionLoggingSubscriber and "fatal" errors logged by
        // _drupal_log_error() as "unhandled" exceptions.
        if (!$context['exception'] instanceof HttpExceptionInterface || $context['exception']->getStatusCode() >= 500) {
          $backtrace = debug_backtrace(0, 3);
          if ((isset($backtrace[2]['class']) && $backtrace[2]['class'] === ExceptionLoggingSubscriber::class && $backtrace[2]['function'] === 'onError') || (!isset($backtrace[2]['class']) && isset($backtrace[2]['function']) && $backtrace[2]['function'] === '_drupal_log_error' && !empty($backtrace[2]['args'][1]))) {
            $eventHint['mechanism'] = new ExceptionMechanism(ExceptionMechanism::TYPE_GENERIC, FALSE);
          }
        }
      }
      $start = microtime(TRUE);
      $rateLimit = $config->get('rate_limit');
      if (!$rateLimit || $counter < $rateLimit) {
        \Sentry\captureEvent($event, EventHint::fromArray($eventHint));
      }
      elseif ($counter == $rateLimit) {
        \Sentry\captureException(new RateLimitException('Log event discarded due to rate limit exceeded; future log events will not be captured by Sentry.'));
      }
      $counter++;
      if ($parent = SentrySdk::getCurrentHub()->getSpan()) {
        $span = new SpanContext();
        $span->setOp('sentry.capture');
        $span->setDescription($context['channel'] . ': ' . $formatted_message);
        $span->setStartTimestamp($start);
        $span->setEndTimestamp(microtime(TRUE));
        $parent->startChild($span);
      }
    }

    // Record a breadcrumb.
    $breadcrumb = [
      'level' => $level,
      'message' => $message,
      'context' => $context,
      'process' => TRUE,
      'breadcrumb' => [
        'category' => $context['channel'],
        'message' => isset($formatted_message) ? (string) $formatted_message : NULL,
        'level' => $levels[$level] ?? Breadcrumb::LEVEL_INFO,
      ],
    ];
    foreach (['%line', '%file', '%type', '%function'] as $key) {
      if (isset($context[$key])) {
        $breadcrumb['breadcrumb']['data'][substr($key, 1)] = $context[$key];
      }
    }
    if (!$config->get('disable_deprecated_alter')) {
      $this->moduleHandler->alter('raven_breadcrumb', $breadcrumb);
    }
    if (!empty($breadcrumb['process'])) {
      \Sentry\addBreadcrumb(Breadcrumb::fromArray($breadcrumb['breadcrumb']));
    }
  }

  /**
   * Sends all unsent events.
   *
   * Call this method periodically if you have a long-running script or are
   * processing a large set of data which may generate errors.
   */
  public function flush(): void {
    if ($client = $this->getClient()) {
      $client->flush();
    }
  }

}
