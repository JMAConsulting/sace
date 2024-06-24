<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Helper\Dumper;

use DrupalCodeGenerator\Asset\AssetCollection;

/**
 * An interface for asset dumpers.
 */
interface DumperInterface {

  /**
   * Dumps the generated code to file system or stdout.
   */
  public function dump(AssetCollection $assets, string $destination): AssetCollection;

}
