<?php

namespace Drupal\xhprof\XHProfLib\Storage;

/**
 * Provides interface for storage.
 */
interface StorageInterface {

  /**
   * Returns a list of stored runs.
   *
   * @todo Add paging.
   *
   * @return array
   *   The array of metadata for each run.
   */
  public function getRuns();

  /**
   * Loads run.
   *
   * @param string $run_id
   *   The run ID.
   * @param string $namespace
   *   The run namespace.
   *
   * @return \Drupal\xhprof\XHProfLib\Run
   *   The value object of the run.
   */
  public function getRun($run_id, $namespace);

  /**
   * Saves run data.
   *
   * @param array $data
   *   The data.
   * @param string $namespace
   *   The run namespace.
   * @param string $run_id
   *   The run ID.
   *
   * @return string
   *   The run ID.
   */
  public function saveRun($data, $namespace, $run_id);

  /**
   * Returns run name.
   *
   * @return string
   *   The name.
   */
  public function getName();

}
