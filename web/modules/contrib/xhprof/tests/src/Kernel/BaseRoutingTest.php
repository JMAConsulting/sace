<?php

namespace Drupal\Tests\xhprof\Kernel;

use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests routing generation.
 *
 * @group xhprof
 */
class BaseRoutingTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['xhprof'];

  /**
   * Tests xhprof.symbol route.
   *
   * @param string $symbol
   *   The symbol to pass to URL generator.
   * @param string $expected
   *   Expected result in URL.
   *
   * @dataProvider dataSymbols
   */
  public function testSymbolRoute($symbol, $expected) {
    $run = 1;
    $url = Url::fromRoute('xhprof.symbol', [
      'run' => $run,
      'symbol' => $symbol,
    ]);
    $this->assertSame('/admin/reports/xhprof/' . $run . '/symbol/' . $expected, $url->toString());
  }

  /**
   * Data provider for ::testSymbolRoute()
   *
   * @return array
   *   Test cases.
   */
  public function dataSymbols() {
    return [
      'backslash' => [
        'Drupal\xhprof\EventSubscriber\XHProfEventSubscriber::onKernelTerminate',
        'Drupal%5Cxhprof%5CEventSubscriber%5CXHProfEventSubscriber%3A%3AonKernelTerminate',
      ],
      'slash' => [
        'load::Controller/NodeController.php',
        'load%3A%3AController/NodeController.php',
      ],
    ];
  }

}
