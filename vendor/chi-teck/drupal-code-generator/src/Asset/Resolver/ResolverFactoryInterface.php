<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Asset\Resolver;

use DrupalCodeGenerator\InputOutput\IO;

/**
 * Interface for classes capable of creating resolvers.
 */
interface ResolverFactoryInterface {

  /**
   * Creates a resolver.
   */
  public static function createResolver(IO $io, mixed $options): ResolverInterface;

}
