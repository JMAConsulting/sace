<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Asset\Resolver;

use DrupalCodeGenerator\Asset\Asset;

/**
 * Interface resolver.
 *
 * A resolver is called when the asset with the same path already exists in the
 * file system. The purpose of the resolver is to merge the existing asset with
 * the one provided by a generator.
 */
interface ResolverInterface {

  /**
   * Resolves an asset.
   *
   * Returns the resolved asset or NULL if existing asset is up-to-date.
   *
   * @throw \InvalidArgumentException
   */
  public function resolve(Asset $asset, string $path): ?Asset;

}
