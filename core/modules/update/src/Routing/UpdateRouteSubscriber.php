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
    // If we are on the status report or batch page do not display the update
    // messages.
    foreach (['system.status', 'system.batch_page.html', 'system.theme_install'] as $skipped_route) {
      if ($route = $collection->get($skipped_route)) {
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
