<?php

namespace Drupal\raven\Http;

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
        $request = $request->withHeader('sentry-trace', \Sentry\getTraceparent());
        $hub = SentrySdk::getCurrentHub();
        if ($parent = $hub->getSpan()) {
          $context = new SpanContext();
          $context->setOp('http.client');
          $context->setDescription($request->getMethod() . ' ' . (string) $partialUri);
          $context->setData([
            'http.url' => (string) $partialUri,
            'http.request.method' => $request->getMethod(),
            'http.query' => $request->getUri()->getQuery(),
            'http.fragment' => $request->getUri()->getFragment(),
          ]);
          $span = $parent->startChild($context);
          $hub->setSpan($span);
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
