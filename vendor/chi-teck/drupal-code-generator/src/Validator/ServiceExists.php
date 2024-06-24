<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Validator;

use DrupalCodeGenerator\Helper\Drupal\ServiceInfo;

/**
 * Validates that service with a given name exists.
 */
final class ServiceExists {

  /**
   * Constructs the object.
   */
  public function __construct(
    private readonly ServiceInfo $serviceInfo,
  ) {}

  /**
   * @throws \UnexpectedValueException
   */
  public function __invoke(string $value): string {
    if (!$this->serviceInfo->getServiceDefinition($value)) {
      throw new \UnexpectedValueException('Service does not exists.');
    }
    return $value;
  }

}
