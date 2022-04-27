<?php

namespace Drupal\system\Routing;

use Symfony\Component\Routing\Route;

/**
 * Dynamically defines routes for menu linkset endpoints.
 */
class MenuLinksetRoutes {

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $routes = [];

    // Only enable linkset routes if the related config option is enabled.
    if (\Drupal::config('system.linkset')->get('enable_endpoint')) {
      $routes['system.menu.linkset'] = new Route(
        '/system/menu/{menu}/linkset',
        [
          '_controller' => 'Drupal\system\Controller\Linkset::process',
        ],
        [
          '_access' => 'TRUE',
        ],
        [
          'parameters' => [
            'menu' => [
              'type' => 'entity:menu',
            ],
          ],
        ]
      );
    }
    return $routes;
  }

}
