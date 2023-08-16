<?php

namespace Drupal\system\Access;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
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
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menuLinkManager
   *   The menu link manager service.
   */
  public function __construct(
    private readonly AccessManagerInterface $accessManager,
    private readonly MenuLinkTreeInterface $menuLinkTree,
    private readonly AccessAwareRouter $router,
    private readonly MenuLinkManagerInterface $menuLinkManager,
  ) {
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
    // Load links matching this route.
    $route = $route_match->getRouteObject();
    $parameters = $route_match->getParameters()->getIterator()->getArrayCopy();
    $parameters_without_defaults = $parameters;
    $route_defaults = $route->getDefaults();
    foreach (array_keys($parameters) as $key) {
      if (isset($route_defaults[$key]) && $route_defaults[$key] === $parameters[$key]) {
        unset($parameters_without_defaults[$key]);
      }
    }
    // First try to find the menu link using all specified parameters.
    $links = $this->menuLinkManager->loadLinksByRoute($route_match->getRouteName(), $parameters, 'admin');
    // If the menu link was not found, try finding it without the parameters
    // that matched the route defaults. Depending on whether the parameter is
    // specified in the menu item with a value matching the default or not
    // specified at all will change how it is stored in the menu_tree table but
    // in either case the route match parameters will always include the default
    // parameters. This fallback method of finding the menu item is needed so
    // that menu items will work in either case.
    if (empty($links)) {
      $links = $this->menuLinkManager->loadLinksByRoute($route_match->getRouteName(), $parameters_without_defaults, 'admin');
    }
    if (empty($links)) {
      // If we did not find a link then we have no opinion on access.
      return AccessResult::neutral();
    }
    return $this->hasAccessToChildMenuItems(reset($links), $account)->cachePerPermissions();
  }

  /**
   * Check that the given route has access to one of it's child routes.
   *
   * @param \Drupal\Core\Menu\MenuLinkInterface $link
   *   The menu link.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  protected function hasAccessToChildMenuItems(MenuLinkInterface $link, AccountInterface $account): AccessResultInterface {
    $parameters = new MenuTreeParameters();
    $parameters->setRoot($link->getPluginId())
      ->excludeRoot()
      ->setTopLevelOnly()
      ->onlyEnabledLinks();

    $tree = $this->menuLinkTree->load(NULL, $parameters);

    if (empty($tree)) {
      $route = $this->router->getRouteCollection()->get($link->getRouteName());
      if ($route) {
        return AccessResult::allowedIf(empty($route->getRequirement('_access_admin_menu_block_page')));
      }
      return AccessResult::neutral();
    }

    foreach ($tree as $element) {
      if (!$this->accessManager->checkNamedRoute($element->link->getRouteName(), $element->link->getRouteParameters(), $account)) {
        continue;
      }

      // Check if it's again route with inaccessible children.
      return AccessResult::allowedIf($this->hasAccessToChildMenuItems($element->link, $account)->isAllowed());
    }

    return AccessResult::neutral();
  }

}
