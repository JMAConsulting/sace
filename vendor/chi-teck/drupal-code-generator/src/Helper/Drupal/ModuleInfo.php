<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Helper\Drupal;

use Drupal\Core\Extension\Extension;
<<<<<<< HEAD
=======
use Drupal\Core\Extension\ModuleExtensionList;
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Filesystem\Path;

/**
 * A helper that provides information about installed Drupal modules.
 */
final class ModuleInfo extends Helper implements ExtensionInfoInterface {

  /**
   * Constructs the object.
   */
<<<<<<< HEAD
  public function __construct(private readonly ModuleHandlerInterface $moduleHandler) {}
=======
  public function __construct(
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly ModuleExtensionList $moduleList,
  ) {}
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'module_info';
  }

  /**
   * {@inheritdoc}
   *
   * @psalm-return array<string, string>
   */
  public function getExtensions(): array {
    $modules = [];
    foreach ($this->moduleHandler->getModuleList() as $machine_name => $module) {
<<<<<<< HEAD
      $modules[$machine_name] = $this->moduleHandler->getName($machine_name);
=======
      /** @psalm-suppress InternalMethod */
      $modules[$machine_name] = $this->moduleList->getName($machine_name);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    }
    return $modules;
  }

  /**
   * {@inheritdoc}
   */
  public function getDestination(string $machine_name, bool $is_new): string {
    $modules_dir = \is_dir(\DRUPAL_ROOT . '/modules/custom') ?
      'modules/custom' : 'modules';

    if ($is_new) {
      $destination = $modules_dir;
    }
    else {
      $destination = \array_key_exists($machine_name, $this->getExtensions())
        ? $this->moduleHandler->getModule($machine_name)->getPath()
        : $modules_dir . '/' . $machine_name;
    }

    return \DRUPAL_ROOT . '/' . $destination;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionName(string $machine_name): ?string {
    return $this->getExtensions()[$machine_name] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionMachineName(string $name): ?string {
    return \array_search($name, $this->getExtensions()) ?: NULL;
  }

  /**
   * Gets module info for a given absolute path.
   */
  public function getExtensionFromPath(string $path): ?Extension {
    if (!Path::isAbsolute($path)) {
      throw new \InvalidArgumentException('The path must be absolute.');
    }
    foreach ($this->moduleHandler->getModuleList() as $module) {
      if (\str_starts_with($path, \DRUPAL_ROOT . '/' . $module->getPath())) {
        return $module;
      }
    }
    return NULL;
  }

}
