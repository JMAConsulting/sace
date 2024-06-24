<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Attribute;

use DrupalCodeGenerator\GeneratorType;

/**
 * Generator definition.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Generator {

  public function __construct(
    public readonly string $name,
    public readonly string $description = '',
    public readonly array $aliases = [],
    public readonly bool $hidden = FALSE,
    public readonly ?string $templatePath = NULL,
    public readonly GeneratorType $type = GeneratorType::OTHER,
    public readonly ?string $label = NULL,
  ) {}

}
