<?php

namespace Drupal\xhprof\Routing;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\xhprof\ProfilerInterface;
use Symfony\Component\Routing\Route;

/**
 * Casts run identifier into Run object.
 *
 * @see \Drupal\xhprof\Controller\XHProfController::runAction()
 * @see \Drush\Commands\core\XhprofCommands::xhprofPost()
 */
class RunConverter implements ParamConverterInterface {

  /**
   * The profiler.
   *
   * @var \Drupal\xhprof\ProfilerInterface
   */
  private $profiler;

  /**
   * Constructs the RunConverter object
   *
   * @param \Drupal\xhprof\ProfilerInterface $profiler
   *   A profiler.
   */
  public function __construct(ProfilerInterface $profiler) {
    $this->profiler = $profiler;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    try {
      return $this->profiler->getRun($value);
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    if (!empty($definition['type']) && $definition['type'] === 'xhprof:run_id') {
      return TRUE;
    }
    return FALSE;
  }

}
