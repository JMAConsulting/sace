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

// @phpstan-ignore-next-line for compatibility with older Guzzle versions.
use function GuzzleHttp\Promise\rejection_for;

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
        $hub = NULL;
        $span = NULL;
        $parent = NULL;
        // Build URI without username/password.
        $partialUri = Uri::fromParts([
          'scheme' => $request->getUri()->getScheme(),
          'host' => $request->getUri()->getHost(),
          'port' => $request->getUri()->getPort(),
          'path' => $request->getUri()->getPath(),
        ]);
        if (function_exists('Sentry\getTraceparent')) {
          $request = $request->withHeader('sentry-trace', \Sentry\getTraceparent());
        }
        if (class_exists(SentrySdk::class)) {
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
            if (!function_exists('Sentry\getTraceparent')) {
              $request = $request->withHeader('sentry-trace', $span->toTraceparent());
            }
          }
        }
        $handlerPromiseCallback = static function ($responseOrException) use ($hub, $request, $span, $parent, $partialUri) {
          if ($hub) {
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
          }
          if ($responseOrException instanceof \Throwable) {
            // @phpstan-ignore-next-line for compatibility with older Guzzle versions.
            return class_exists(Create::class) ? Create::rejectionFor($responseOrException) : rejection_for($responseOrException);
          }
          return $responseOrException;
        };
        return $handler($request, $options)->then($handlerPromiseCallback, $handlerPromiseCallback);
      };
    };
  }

}
