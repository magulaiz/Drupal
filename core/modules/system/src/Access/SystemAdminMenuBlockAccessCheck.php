<?php

namespace Drupal\system\Access;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access check for routes implementing _access_admin_menu_block_page.
 */
class SystemAdminMenuBlockAccessCheck implements AccessInterface {

  /**
   * The access manager.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * Constructs a new SystemAdminMenuBlockAccessCheck.
   *
   * @param \Drupal\Core\Access\AccessManagerInterface $access_manager
   *   The access manager.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_link_tree
   *   The menu link tree service.
   */
  public function __construct(AccessManagerInterface $access_manager, MenuLinkTreeInterface $menu_link_tree) {
    $this->accessManager = $access_manager;
    $this->menuLinkTree = $menu_link_tree;
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
   * @param string $route_id
   *   The route ID.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function hasAccessToChildRoutes(string $route_id, AccountInterface $account): AccessResultInterface {
    $parameters = new MenuTreeParameters();
    $parameters->setRoot($route_id)
      ->excludeRoot()
      ->setTopLevelOnly()
      ->onlyEnabledLinks();

    $tree = $this->menuLinkTree->load(NULL, $parameters);

    if (empty($tree)) {
      return AccessResult::allowed();
    }

    foreach ($tree as $menu_link_route_id => $element) {
      $url = $element->link->getUrlObject();
      if (!$this->accessManager->checkNamedRoute($url->getRouteName(), $url->getRouteParameters(), $account)) {
        continue;
      }

      // Check if it's again route with inaccessible children.
      return AccessResult::allowedIf($this->hasAccessToChildRoutes($menu_link_route_id, $account)->isAllowed());
    }

    return AccessResult::neutral();
  }

}
