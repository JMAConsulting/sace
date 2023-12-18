<?php

namespace Drupal\xhprof\XHProfLib\Storage;

/**
 * Provides service to collect available storage services.
 */
class StorageManager {

  /**
   * @var \Drupal\xhprof\XHProfLib\Storage\StorageInterface[]
   */
  private $storages;

  /**
   * @return array
   */
  public function getStorages() {
    $output = [];

    foreach ($this->storages as $id => $storage) {
      $output[$id] = $storage->getName();
    }

    return $output;
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\Storage\StorageInterface $storage
   *
   * @see \Drupal\xhprof\Compiler\StoragePass::process()
   */
  public function addStorage($id, StorageInterface $storage) {
    $this->storages[$id] = $storage;
  }

}
