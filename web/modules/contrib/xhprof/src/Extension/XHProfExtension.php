<?php

namespace Drupal\xhprof\Extension;

/**
 * Implements support for PHP 7 XHProf extension.
 *
 * @see https://github.com/longxinH/xhprof
 */
class XHProfExtension implements ExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public static function isLoaded() {
    return extension_loaded('xhprof');
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return [
      'FLAGS_CPU' => 'XHPROF_FLAGS_CPU',
      'FLAGS_MEMORY' => 'XHPROF_FLAGS_MEMORY',
      'FLAGS_NO_BUILTINS' => 'XHPROF_FLAGS_NO_BUILTINS',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function enable($modifier, $options) {
    xhprof_enable($modifier, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function disable() {
    return xhprof_disable();
  }

}
