<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Validator;

/**
 * Validates that the value is one of the expected values.
 */
final class Choice {

  public function __construct(
    private readonly array $choices,
    private readonly string $message = 'The value you selected is not a valid choice.',
  ) {}

  /**
   * @throws \UnexpectedValueException
   */
  public function __invoke(mixed $value): string|int|float {
    return match(FALSE) {
      \is_string($value) || \is_int($value) || \is_float($value),
      \in_array($value, $this->choices, TRUE) => throw new \UnexpectedValueException($this->message),
      default => $value,
    };
  }

}
