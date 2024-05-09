<?php

namespace Drupal\raven\Http;

use Drupal\raven\EventSubscriber\RequestSubscriber;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sentry\Breadcrumb;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;

/**
 * Instrument Guzzle HTTP requests.
 */
class HttpClientMiddleware {

  public function __construct(protected ?RequestSubscriber $requestSubscriber = NULL) {
  }

  /**
   * {@inheritdoc}
   */
  public function __invoke(): callable {
    return function ($handler) {
      return function (RequestInterface $request, array $options) use ($handler) {
        $span = NULL;
        $parent = NULL;
        // Build URI without username/password.
        $partialUri = Uri::fromParts([
          'scheme' => $request->getUri()->getScheme(),
          'host' => $request->getUri()->getHost(),
          'port' => $request->getUri()->getPort(),
          'path' => $request->getUri()->getPath(),
        ]);
        $hub = SentrySdk::getCurrentHub();
        if ($parent = $hub->getSpan()) {
          $context = SpanContext::make()
            ->setOp('http.client')
            ->setDescription($request->getMethod() . ' ' . (string) $partialUri)
            ->setData([
              'http.url' => (string) $partialUri,
              'http.request.method' => $request->getMethod(),
              'http.query' => $request->getUri()->getQuery(),
              'http.fragment' => $request->getUri()->getFragment(),
            ]);
          $span = $parent->startChild($context);
          $hub->setSpan($span);
        }
        if ($client = $hub->getClient()) {
          $targets = $client->getOptions()->getTracePropagationTargets();
          if ($targets === NULL || in_array($request->getUri()->getHost(), $targets)) {
            $request = $request
              ->withHeader('sentry-trace', \Sentry\getTraceparent())
              ->withHeader('traceparent', \Sentry\getW3CTraceparent());
          }
          if ($targets !== NULL && in_array($request->getUri()->getHost(), $targets) && $this->requestSubscriber) {
            $this->requestSubscriber->sanitizeBaggage();
            $request = $request->withHeader('baggage', \Sentry\getBaggage());
          }
        }
        $handlerPromiseCallback = static function ($responseOrException) use ($hub, $request, $span, $parent, $partialUri) {
          if ($span) {
            $span->finish();
            $hub->setSpan($parent);
          }
          $response = NULL;
          if ($responseOrException instanceof ResponseInterface) {
            $response = $responseOrException;
          }
          elseif ($responseOrException instanceof RequestException) {
            $response = $responseOrException->getResponse();
          }
          $breadcrumbData = [
            'http.url' => (string) $partialUri,
            'http.request.method' => $request->getMethod(),
            'http.request.body.size' => $request->getBody()->getSize(),
          ];
          if ('' !== $request->getUri()->getQuery()) {
            $breadcrumbData['http.query'] = $request->getUri()->getQuery();
          }
          if ('' !== $request->getUri()->getFragment()) {
            $breadcrumbData['http.fragment'] = $request->getUri()->getFragment();
          }
          if ($response) {
            if ($span) {
              $span->setStatus(SpanStatus::createFromHttpStatusCode($response->getStatusCode()));
            }
            $breadcrumbData['http.response.status_code'] = $response->getStatusCode();
            $breadcrumbData['http.response.body.size'] = $response->getBody()->getSize();
          }
          elseif ($span) {
            $span->setStatus(SpanStatus::internalError());
          }
          $hub->addBreadcrumb(new Breadcrumb(Breadcrumb::LEVEL_INFO, Breadcrumb::TYPE_HTTP, 'http', NULL, $breadcrumbData));
          if ($responseOrException instanceof \Throwable) {
            return Create::rejectionFor($responseOrException);
          }
          return $responseOrException;
        };
        return $handler($request, $options)->then($handlerPromiseCallback, $handlerPromiseCallback);
      };
    };
  }

}
