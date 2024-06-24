<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Command\PhpStormMeta;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use DrupalCodeGenerator\Asset\File;
use DrupalCodeGenerator\Utils;

/**
 * Generates PhpStorm meta-data for entity links.
 */
final class EntityLinks {

  /**
   * Constructs the object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly \Closure $entityInterface,
  ) {}

  /**
   * Generator callback.
   */
  public function __invoke(): File {
    $definitions = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type => $definition) {
      $definitions[] = [
        'type' => $entity_type,
        'label' => $definition->getLabel(),
        'class' => Utils::addLeadingSlash($definition->getClass()),
        'interface' => ($this->entityInterface)($definition),
        'links' => \array_keys($definition->getLinkTemplates()),
      ];
    }
    \asort($definitions);

    return File::create('.phpstorm.meta.php/entity_links.php')
      ->template('entity_links.php.twig')
      ->vars(['definitions' => $definitions]);
  }

}
