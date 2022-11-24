<?php
namespace Drupal\pe_presentation_evaluation\Controller;

use Drupal\Core\Controller\ControllerBase;

class TestController extends ControllerBase {

  public function test() {
    return [
      '#markup' => 'Presentation Evaluation Module Test',
    ];
  }

}
