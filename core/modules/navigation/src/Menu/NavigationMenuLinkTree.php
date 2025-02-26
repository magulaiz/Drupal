<?php

declare(strict_types=1);

namespace Drupal\navigation\Menu;

use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Extends MenuLinkTree to add specific theme suggestions for the navigation.
 *
 * @internal
 */
final class NavigationMenuLinkTree extends MenuLinkTree {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function build(array $tree): array {
    if (!$tree) {
      return [];
    }
    $build = parent::build($tree);

    if (empty($build['#items'])) {
      return [];
    }

    /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
    $first_link = reset($tree)->link;
    // Get the menu name of the first link.
    $menu_name = $first_link->getMenuName();
    // Add a more specific theme suggestion to differentiate this rendered
    // menu from others.
    $build['#menu_name'] = $menu_name;
    $build['#theme'] = 'navigation_menu__' . strtr($menu_name, '-', '_');

    foreach ($tree as $item) {
      if ($item->access->isAllowed()) {

        // Add the plugin id as a class.
        $plugin_id = $item->link->getPluginId();
        $plugin_class = str_replace('.', '_', $plugin_id);
        $build['#items'][$plugin_id]['class'] = $plugin_class;

        // Add an overview menu link to all pages that are not known as simply
        // listing their children.
        foreach ($item->subtree as $sub_item) {
          $route_name = $sub_item->link->getRouteName();
          if (
            !empty($build['#items'][$plugin_id]['below'][$route_name]['below'])
            && $this->routeProvider->getRouteByName($route_name)->getDefault('_controller') !== '\Drupal\system\Controller\SystemController::systemAdminMenuBlockPage'
          ) {

            // Clone the parent link, changing the title to 'Overview' and
            // add it to the top of the menu children.
            $overview = $build['#items'][$plugin_id]['below'][$route_name];
            $overview['title'] = $this->t('Overview');
            $overview['below'] = [];
            $build['#items'][$plugin_id]['below'][$route_name]['below'] = [
              $route_name => $overview,
            ] + $build['#items'][$plugin_id]['below'][$route_name]['below'];
          }
        }
      }
    }

    return $build;
  }

}
