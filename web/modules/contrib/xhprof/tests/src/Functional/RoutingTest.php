<?php

namespace Drupal\Tests\xhprof\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests behaviors when visiting the reports and settings pages.
 *
 * @group xhprof
 */
class RoutingTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['xhprof'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests access permissions to every route provided by the module.
   */
  public function testRoutes() {
    $urls = [
      Url::fromRoute('xhprof.admin_configure'),
      Url::fromRoute('xhprof.runs'),
      // @TODO create fixture runs to view and test.
      //Url::fromRoute('xhprof.diff', ['run1' => '', 'run2' => '']),
      //Url::fromRoute('xhprof.symbol', ['run' => '', 'symbol' => 'file_exists']),
      //Url::fromRoute('xhprof.run', ['run' => 'toGenerate']),
    ];
    // Ensures Anonymous visitor getting no-access.
    foreach ($urls as $url) {
      $this->assertRouteStatusCode($url, 403);
    }
    $permissions = [
      'access xhprof data' => ['xhprof.admin_configure'],
      'administer xhprof' => [],
    ];
    // Make sure authorized getting access to all route except excluded.
    foreach ($permissions as $permission => $exclude) {
      $this->drupalLogin($this->drupalCreateUser([$permission], $permission));
      /** @var \Drupal\Core\Url $url */
      foreach ($urls as $url) {
        $code = in_array($url->getRouteName(), $exclude) ? 403 : 200;
        $this->assertRouteStatusCode($url, $code);
      }
    }
  }

  /**
   * Asserts that URL returns expected HTTP status code.
   *
   * @param \Drupal\Core\Url $url
   *   The URL to access.
   * @param int $code
   *   Expected status code in response.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   When status code is not expected.
   */
  protected function assertRouteStatusCode(Url $url, int $code) {
    $this->drupalGet($url);
    $this->assertSession()->statusCodeEquals($code);
  }

}
