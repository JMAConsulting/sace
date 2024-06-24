<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Validator;

use DrupalCodeGenerator\GeneratorType;
use DrupalCodeGenerator\Helper\Drupal\ExtensionInfoInterface;

/**
 * Validates that the extension with a given name exists.
 */
final class ExtensionExists {

  /**
   * Constructs the object.
   */
  public function __construct(
    private readonly ExtensionInfoInterface $extensionInfo,
    private readonly GeneratorType $generatorType,
  ) {}

  /**
   * @throws \UnexpectedValueException
   */
  public function __invoke(string $value): string {
    if (!\array_key_exists($value, $this->extensionInfo->getExtensions())) {
      $format = match($this->generatorType) {
        GeneratorType::MODULE_COMPONENT => 'Module "%s" does not exists.',
        GeneratorType::THEME_COMPONENT => 'Theme "%s" does not exists.',
        default => 'Extension "%s" does not exists.',
      };
      throw new \UnexpectedValueException(\sprintf($format, $value));
    }
    return $value;
  }

}
