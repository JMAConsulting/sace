<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Event;

/**
 * Fired when altering registered generators.
 */
final class GeneratorInfoAlter {

  /**
   * @psalm-param list<\Symfony\Component\Console\Command\Command> $generators
   */
  public array $generators = [];

  /**
   * Constructs the event object.
   *
   * @psalm-param list<\Symfony\Component\Console\Command\Command> $generators
   */
  public function __construct(array $generators) {
    // Index generators by name to ease access.
    foreach ($generators as $generator) {
      /** @psalm-suppress PossiblyNullArrayOffset */
      $this->generators[$generator->getName()] = $generator;
    }
  }

}
