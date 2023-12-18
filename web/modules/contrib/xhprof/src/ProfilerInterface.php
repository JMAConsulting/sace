<?php

namespace Drupal\xhprof;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides interface to interact with profiler.
 */
interface ProfilerInterface {

  /**
   * Conditionally enable XHProf profiling.
   */
  public function enable();

  /**
   * Shutdown and disable XHProf profiling.
   *
   * Report is saved with selected storage.
   *
   * @return string
   *   The run ID.
   */
  public function shutdown($runId);

  /**
   * Check whether profiler is enabled.
   *
   * @return boolean
   *   TRUE when enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Returns whether a profiling can be enabled for the current request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return bool
   *   TRUE profiling can be enabled, FALSE otherwise.
   */
  public function canEnable(Request $request);

  /**
   * Is any profiler extension loaded.
   *
   * @return bool
   *   TRUE when more then one PHP supported profiler enabled, FALSE otherwise.
   */
  public function isLoaded();

  /**
   * Returns a list of available PHP extensions for profiling.
   *
   * @return array
   *   Keyed array of extension name and its description.
   */
  public function getExtensions();

  /**
   * Generates a link to the report page for a specific run ID.
   *
   * @param string $run_id
   *   The run ID.
   *
   * @return string
   *   The rendered link.
   */
  public function link($run_id);

  /**
   * Returns the current selected storage.
   *
   * @return \Drupal\xhprof\XHProfLib\Storage\StorageInterface
   *   The storage.
   */
  public function getStorage();

  /**
   * Returns the run id associated with the current request.
   *
   * @return string
   *   The run ID.
   */
  public function getRunId();

  /**
   * Creates a new unique run id.
   *
   * @return string
   *   The run ID.
   */
  public function createRunId();

  /**
   * Loads a specific run.
   *
   * @param string $run_id
   *   The run ID.
   *
   * @return \Drupal\xhprof\XHProfLib\Run
   *   The run object.
   */
  public function getRun($run_id);

}
