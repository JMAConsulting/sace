<?php

namespace Drupal\raven\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Sentry\Dsn;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller routines for tunneling Sentry requests.
 */
class TunnelController extends ControllerBase {

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a new TunnelController instance.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The theme handler.
   */
  final public function __construct(ClientInterface $httpClient) {
    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('http_client')
    );
  }

  /**
   * Passes the incoming request on to the Sentry server.
   *
   * @return \Psr\Http\Message\ResponseInterface|\Symfony\Component\HttpFoundation\Response
   *   A PSR-7 or Symfony response.
   */
  public function doTunnel(Request $request) {
    $config = $this->config('raven.settings');
    $configured_dsn = empty($_SERVER['SENTRY_DSN']) ? $config->get('public_dsn') : $_SERVER['SENTRY_DSN'];
    $configured_dsn = Dsn::createFromString($configured_dsn);

    $envelope = $request->getContent();

    $pieces = explode("\n", $envelope, 2);
    $header = json_decode($pieces[0], TRUE);
    if (!isset($header["dsn"])) {
      return new Response(NULL, Response::HTTP_BAD_REQUEST);
    }
    $dsn = Dsn::createFromString($header["dsn"]);

    if (!hash_equals((string) $configured_dsn, (string) $dsn)) {
      return new Response(NULL, Response::HTTP_BAD_REQUEST);
    }

    $url = $dsn->getEnvelopeApiEndpointUrl();

    try {
      $options = [
        'headers' => [
          'Content-type' => 'application/x-sentry-envelope',
        ],
        'body' => $envelope,
        // Add Sentry key for GlitchTip compatibility.
        'query' => [
          'sentry_key' => $dsn->getPublicKey(),
        ],
      ];
      $timeout = $config->get('timeout');
      if (NULL !== $timeout) {
        $options['connect_timeout'] = $options['timeout'] = $timeout;
      }
      $response = $this->httpClient->request('POST', $url, $options);
    }
    catch (ClientException $e) {
      return $e->getResponse();
    }

    return $response;
  }

}
