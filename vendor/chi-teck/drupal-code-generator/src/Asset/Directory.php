<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Asset;

use DrupalCodeGenerator\Asset\Resolver\PreserveResolver;
use DrupalCodeGenerator\Asset\Resolver\ResolverDefinition;

/**
 * Simple data structure to represent a directory being created.
 */
final class Directory extends Asset {

  /**
   * {@inheritdoc}
   */
  public function __construct(string $path) {
    parent::__construct($path);
    $this->mode(0755);
    // Recreating existing directories makes no sense.
    $this->resolverDefinition = new ResolverDefinition(PreserveResolver::class);
  }

  /**
   * Named constructor.
   */
  public static function create(string $path): self {
    return new self($path);
  }

}
