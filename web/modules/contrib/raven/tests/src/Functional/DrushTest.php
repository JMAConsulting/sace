<?php

namespace Drupal\Tests\raven\Functional;

use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * @coversDefaultClass \Drupal\raven\Commands\RavenCommands
 *
 * @group raven
 */
class DrushTest extends BrowserTestBase {

  use DrushTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['raven'];

  /**
   * Tests drush commands.
   */
  public function testCommands(): void {
    $this->drush('raven:captureMessage');
    $output = $this->getOutputRaw();
    $this->assertEmpty($output);
    $errorOutput = $this->getErrorOutputRaw();
    $this->assertStringContainsString('[success] Message sent as event', $errorOutput);
    // Test capturing a message with debug logging enabled.
    $this->drush('raven:captureMessage --debug');
    $errorOutput = $this->getErrorOutputRaw();
    $this->assertStringContainsString('The "Sentry\Integration\RequestIntegration" integration has been installed.', $errorOutput);
  }

}
