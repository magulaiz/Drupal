<?php

namespace Drupal\update\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Sets the _omit_update_message for the system.status routes.
 */
class UpdateRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $status_route = $collection->get('system.status');
    $status_route->setOption('_omit_update_message', TRUE);
  }

}
