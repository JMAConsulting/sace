<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Validator;

/**
 * Validates required PHP class name.
 */
final class RequiredClassName {

  /**
   * @throws \UnexpectedValueException
   */
  public function __invoke(mixed $value): string {
    return (new Chained(new Required(), new ClassName()))($value);
  }

}
