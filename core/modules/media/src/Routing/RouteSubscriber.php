<?php

namespace Drupal\media\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.media.field_ui_fields')) {
      $route->setDefault('_controller', '\Drupal\media\Controller\MediaFieldListController::message');
    }
  }


  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {

    // Come after field_ui.
    $events[RoutingEvents::ALTER] = [
      'onAlterRoutes',
      -110,
    ];
    return $events;
  }

}
