<?php

declare(strict_types=1);

namespace Drupal\user_route_alter_test\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Alter the 'user.pass.http' route.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
    if ($route = $collection->get('user.pass.http')) {
      $route->setRequirements([]);
      $route->setRequirement('_access', 'FALSE');
    }
  }

}
