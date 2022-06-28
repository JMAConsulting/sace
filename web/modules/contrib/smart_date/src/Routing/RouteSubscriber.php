<?php

namespace Drupal\smart_date\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('fullcalendar_view.update_event')) {
      $route->setDefault('_controller', "\Drupal\smart_date\Controller\FullCalendarController::updateEvent");
    }
  }

}
