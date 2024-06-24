<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Command\PhpStormMeta;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use DrupalCodeGenerator\Asset\File;

/**
 * Generates PhpStorm meta-data for Drupal states.
 */
final class States {

  /**
   * Constructs the object.
   */
  public function __construct(
    private readonly KeyValueFactoryInterface $keyValueStore,
  ) {}

  /**
   * Generator callback.
   */
  public function __invoke(): File {
    $states = \array_keys($this->keyValueStore->get('state')->getAll());
    return File::create('.phpstorm.meta.php/states.php')
      ->template('states.php.twig')
      ->vars(['states' => $states]);
  }

}
