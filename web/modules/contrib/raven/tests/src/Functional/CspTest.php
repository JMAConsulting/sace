<?php

namespace Drupal\Tests\raven\Functional;

use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests Raven and CSP modules.
 *
 * @group raven
 * @requires modules csp
 */
class CspTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['raven', 'csp'];

  /**
   * Tests Sentry browser client configuration UI.
   */
  public function testRavenJavascriptConfig(): void {
    $admin_user = $this->drupalCreateUser([
      'administer csp configuration',
      'administer site configuration',
      'send javascript errors to sentry',
    ]);
    assert($admin_user instanceof AccountInterface);
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/config/development/logging');
    $this->submitForm([
      'raven[js][javascript_error_handler]' => TRUE,
      'raven[js][public_dsn]' => 'https://a@domain.test/1',
    ], 'Save configuration');

    $this->drupalGet('admin/config/system/csp');
    $this->submitForm([
      'report-only[reporting][handler]' => 'raven',
    ], 'Save configuration');

    $this->assertSession()->responseHeaderEquals('Content-Security-Policy-Report-Only', "object-src 'none'; script-src 'self'; style-src 'self'; frame-ancestors 'self'; report-uri https://domain.test/api/1/security/?sentry_key=a");

    $this->drupalGet('admin/config/system/csp');
    $this->submitForm([
      'report-only[directives][connect-src][enable]' => TRUE,
      'report-only[directives][connect-src][base]' => 'self',
    ], 'Save configuration');

    $this->assertSession()->responseHeaderEquals('Content-Security-Policy-Report-Only', "connect-src 'self' https://domain.test/api/1/envelope/; object-src 'none'; script-src 'self'; style-src 'self'; frame-ancestors 'self'; report-uri https://domain.test/api/1/security/?sentry_key=a");
  }

}
