<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Validator;

/**
 * Validates service name.
 */
final class ServiceName {

  /**
   * @throws \UnexpectedValueException
   */
  public function __invoke(mixed $value): string {
    if (!\is_string($value) || !\preg_match('/^[a-z][a-z0-9_\.]*[a-z0-9]$/', $value)) {
      throw new \UnexpectedValueException('The value is not correct service name.');
    }
    return $value;
  }

}
