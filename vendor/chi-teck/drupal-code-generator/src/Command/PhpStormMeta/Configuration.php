<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Command\PhpStormMeta;

use DrupalCodeGenerator\Asset\File;
use DrupalCodeGenerator\Helper\Drupal\ConfigInfo;

/**
 * Generates PhpStorm meta-data for Drupal configuration.
 */
final class Configuration {

  /**
   * Constructs the object.
   */
  public function __construct(
    private readonly ConfigInfo $configInfo,
  ) {}

  /**
   * Generator callback.
   */
  public function __invoke(): File {
    return File::create('.phpstorm.meta.php/configuration.php')
      ->template('configuration.php.twig')
      ->vars(['configs' => $this->configInfo->getConfigNames()]);
  }

}
