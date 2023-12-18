<?php

namespace Drupal\xhprof\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds available storage handlers to manager in container.
 */
class StoragePass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    if (FALSE === $container->hasDefinition('xhprof.storage_manager')) {
      return;
    }

    $definition = $container->getDefinition('xhprof.storage_manager');

    $services = array_keys($container->findTaggedServiceIds('xhprof_storage'));
    foreach ($services as $id) {
      $definition->addMethodCall('addStorage', [$id, new Reference($id)]);
    }
  }

}
