<?php

namespace Drupal\raven\Controller;

use Drupal\Core\Controller\ControllerBase;
use Sentry\Severity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Sends test message to Sentry.
 */
class TestController extends ControllerBase {

  /**
   * Sends a test message to Sentry.
   */
  public function doTest(Request $request): JsonResponse {
    $id = \Sentry\captureMessage($this->t('Test message @time.', ['@time' => date_create()->format('r')]), new Severity('info'));
    return new JsonResponse(['id' => (string) $id]);
  }

}
