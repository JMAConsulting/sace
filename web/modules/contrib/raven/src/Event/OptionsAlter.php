<?php

namespace Drupal\raven\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event for altering Sentry client options.
 */
class OptionsAlter extends Event {

  /**
   * Sentry client options.
   *
   * @var mixed[]
   */
  public $options;

  /**
   * Create a new OptionsAlter event.
   *
   * @param mixed[] $options
   *   Sentry client options.
   */
  public function __construct(array &$options) {
    $this->options = &$options;
  }

}
