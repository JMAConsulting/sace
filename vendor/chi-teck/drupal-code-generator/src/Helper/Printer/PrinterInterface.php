<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Helper\Printer;

use DrupalCodeGenerator\Asset\AssetCollection;

/**
 * An interface for asset printers.
 */
interface PrinterInterface {

  /**
   * Prints summary.
   */
  public function printAssets(AssetCollection $assets, string $base_path = ''): void;

}
