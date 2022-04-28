<?php

namespace Drupal\update\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Sets the _update_message route option for system routes.
 */
class UpdateRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // The status report page already displays the available updates and the
    // batch patch does not need to display these messages.
    foreach (['system.status', 'system.batch_page.html'] as $skipped_routed) {
      if ($route = $collection->get($skipped_routed)) {
        $route->setOption('_update_message', 'skip');
      }
    }

    // If we are on the appearance or modules list, display a detailed report
    // of the update status.
    foreach (['system.modules_list', 'system.themes_page'] as $verbose_route) {
      if ($route = $collection->get($verbose_route)) {
        $route->setOption('_update_message', 'verbose');
      }
    }
  }

}
