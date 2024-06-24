<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Asset\Resolver;

use DrupalCodeGenerator\Asset\Asset;

final class PreserveResolver implements ResolverInterface {

  /**
   * {@inheritdoc}
   *
   * @psalm-return null
   */
  public function resolve(Asset $asset, string $path): ?Asset {
    return NULL;
  }

}
