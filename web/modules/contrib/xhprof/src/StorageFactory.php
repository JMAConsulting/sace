<?php

namespace Drupal\xhprof;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides storage factory.
 */
class StorageFactory {

  /**
   * Return the configured storage service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container
   *
   * @return \Drupal\xhprof\XHProfLib\Storage\StorageInterface
   *   The storage service.
   */
  final public static function getStorage(ConfigFactoryInterface $config, ContainerInterface $container) {
    $storage = $config->get('xhprof.config')
      ->get('storage') ?: 'xhprof.file_storage';

    return $container->get($storage);
  }

}
