<?php

namespace Drupal\xhprof\Extension;

/**
 * Defines profiling extension.
 */
interface ExtensionInterface {

  /**
   * Returns TRUE if this extension is loaded into the PHP interpreter.
   *
   * @return bool
   *   TRUE when extension loaded or FALSE otherwise.
   */
  public static function isLoaded();

  /**
   * Returns the options supported by this extension.
   *
   * @return array
   *   Keyed array of allowed profiling options for the extension.
   */
  public function getOptions();

  /**
   * Enables the profiling with the extension.
   *
   * @param int $modifier
   *   Flags to add additional information to the profiling.
   * @param array $options
   *   An array of optional options.
   */
  public function enable($modifier, $options);

  /**
   * Disables the extension.
   *
   * @return array
   *   An array of profiling data, from the run.
   */
  public function disable();

}
