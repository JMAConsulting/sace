<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Asset\Resolver;

use DrupalCodeGenerator\Asset\Asset;
use DrupalCodeGenerator\Asset\File;

final class PrependResolver implements ResolverInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve(Asset $asset, string $path): File {
    if (!$asset instanceof File) {
      throw new \InvalidArgumentException('Wrong asset type.');
    }
    $new_content = $asset->getContent();
    $existing_content = \file_get_contents($path);
    return clone $asset->content($new_content . "\n" . $existing_content);
  }

}
