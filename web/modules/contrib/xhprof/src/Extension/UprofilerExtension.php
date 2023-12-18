<?php

namespace Drupal\xhprof\Extension;

/**
 * Implements support for uprofiler extension.
 *
 * @see https://github.com/FriendsOfPHP/uprofiler
 */
class UprofilerExtension implements ExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public static function isLoaded() {
    return extension_loaded('uprofiler');
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return [
      'FLAGS_CPU' => 'UPROFILER_FLAGS_CPU',
      'FLAGS_MEMORY' => 'UPROFILER_FLAGS_MEMORY',
      'FLAGS_NO_BUILTINS' => 'UPROFILER_FLAGS_NO_BUILTINS',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function enable($modifier, $options) {
    uprofiler_enable($modifier, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function disable() {
    return uprofiler_disable();
  }

}
