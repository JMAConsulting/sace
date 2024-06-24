<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Validator;

/**
 * Validates that the value is not empty.
 */
final class Required {

  /**
   * @throws \UnexpectedValueException
   */
  public function __invoke(mixed $value): mixed {
    // FALSE is not considered as empty value because question helper uses
    // it as negative answer on confirmation questions.
    if ($value === NULL || $value === '' || $value === []) {
      throw new \UnexpectedValueException('The value is required.');
    }
    return $value;
  }

}
