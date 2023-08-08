<?php

namespace Drupal\system\Access;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\AccessAwareRouter;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access check for routes implementing _access_admin_menu_block_page.
 */
class SystemAdminMenuBlockAccessCheck implements AccessInterface {

  /**
   * Constructs a new SystemAdminMenuBlockAccessCheck.
   *
   * @param \Drupal\Core\Access\AccessManagerInterface $accessManager
   *   The access manager.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menuLinkTree
   *   The menu link tree service.
   * @param \Drupal\Core\Routing\AccessAwareRouter $router
   *   The router service.
   */
  public function __construct(private readonly AccessManagerInterface $accessManager, private readonly MenuLinkTreeInterface $menuLinkTree, private readonly AccessAwareRouter $router) {
  }

  /**
   * Checks access.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The cron key.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(RouteMatchInterface $route_match, AccountInterface $account): AccessResultInterface {
    return $this->hasAccessToChildRoutes($route_match->getRouteName(), $account)->cachePerPermissions();
  }

  /**
   * Check that the given route has access to one of it's child routes.
   *
   * @param string $route_name
   *   The route ID.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function hasAccessToChildRoutes(string $route_name, AccountInterface $account): AccessResultInterface {
    $parameters = new MenuTreeParameters();
    $parameters->setRoot($route_name)
      ->excludeRoot()
      ->setTopLevelOnly()
      ->onlyEnabledLinks();

    $tree = $this->menuLinkTree->load(NULL, $parameters);

    if (empty($tree)) {
      $route = $this->router->getRouteCollection()->get($route_name);
      if ($route) {
        return AccessResult::allowedIf(empty($route->getRequirement('_access_admin_menu_block_page')));
      }
      return AccessResult::allowed();
    }

    foreach ($tree as $element) {
      $url = $element->link->getUrlObject();
      if (!$this->accessManager->checkNamedRoute($url->getRouteName(), $url->getRouteParameters(), $account)) {
        continue;
      }

      // Check if it's again route with inaccessible children.
      return AccessResult::allowedIf($this->hasAccessToChildRoutes($url->getRouteName(), $account)->isAllowed());
    }

    return AccessResult::neutral();
  }

}
