<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Command\PhpStormMeta;

use DrupalCodeGenerator\Asset\File;

/**
 * Generates PhpStorm meta-data for miscellaneous Drupal methods.
 */
final class Miscellaneous {

  /**
   * Generator callback.
   */
  public function __invoke(): File {
    return File::create('.phpstorm.meta.php/miscellaneous.php')
      ->template('miscellaneous.php.twig');
  }

}
