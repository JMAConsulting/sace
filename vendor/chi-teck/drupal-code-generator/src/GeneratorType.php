<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator;

/**
 * Generator definition.
 *
 * @phpcs:disable
 * @todo Enable code sniffer once it supports enums
 * @see https://www.drupal.org/project/coder/issues/3283741
 */
enum GeneratorType {

  // @todo Support installation profiles.
  case MODULE;
  case MODULE_COMPONENT;
  case THEME;
  case THEME_COMPONENT;
  case OTHER;

  /**
   * Determines if the generator produces a new Drupal extension.
   */
  public function isNewExtension(): bool {
    return $this === self::MODULE || $this === self::THEME;
  }

  /**
   * Defines a label for extension human name.
   */
  public function getNameLabel(): string {
    return match ($this) {
      self::MODULE, self::MODULE_COMPONENT => 'Module name',
      self::THEME, self::THEME_COMPONENT => 'Theme name',
      default => 'Name',
    };
  }

  /**
   * Defines a label for extension machine name.
   */
  public function getMachineNameLabel(): string {
    return match ($this) {
      self::MODULE, self::MODULE_COMPONENT => 'Module machine name',
      self::THEME, self::THEME_COMPONENT => 'Theme machine name',
      default => 'Machine name',
    };
  }

}
