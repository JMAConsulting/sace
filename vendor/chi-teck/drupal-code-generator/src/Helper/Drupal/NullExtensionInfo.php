<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Helper\Drupal;

use Drupal\Core\Extension\Extension;

/**
 * This helper can be used to avoid conditional calls for extension info.
 *
 * @todo Is it still needed?
 */
final class NullExtensionInfo implements ExtensionInfoInterface {

  /**
   * {@inheritdoc}
   */
  public function getExtensions(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getDestination(string $machine_name, bool $is_new): ?string {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionName(string $machine_name): ?string {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionMachineName(string $name): ?string {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionFromPath(string $path): ?Extension {
    return NULL;
  }

}
