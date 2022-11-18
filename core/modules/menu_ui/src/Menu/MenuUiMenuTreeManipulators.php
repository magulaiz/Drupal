<?php

namespace Drupal\menu_ui\Menu;

use Drupal\Core\Access\AccessResult;

/**
 * Provides menu tree manipulators to be used when managing menu links.
 */
class MenuUiMenuTreeManipulators {

  /**
   * Grants access to a menu tree when used in the menu management form.
   *
   * Alternative to DefaultMenuLinkTreeManipulators::checkAccess() to be used in
   * menu tree links manage form, \Drupal\menu_ui\MenuForm. Site builders should
   * be still able to create and manage menu links with Menu UI even if the menu
   * link route is not accessible. Here are some use cases:
   * - A login menu link, using the `user.login` route, is not accessible to a
   *   logged-in user, but the site builder still needs to configure the menu
   *   link.
   * - A site builder wants to create a menu item for Views page that is not yet
   *   created, thus there's no access to the route, as the route doesn't exist.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   The menu link tree to manipulate.
   *
   * @return \Drupal\Core\Menu\MenuLinkTreeElement[]
   *   The manipulated menu link tree.
   *
   * @internal
   *   This menu tree manipulator is intended to be used only in the context of
   *   MenuForm, where the user permissions to administer links is already
   *   checked. Don't use this manipulator in other places.
   *
   * @see \Drupal\Core\Menu\DefaultMenuLinkTreeManipulators::checkAccess()
   * @see \Drupal\menu_ui\MenuForm
   */
  public function checkAccess(array $tree): array {
    foreach ($tree as $key => $element) {
      $tree[$key]->access = AccessResult::allowed();
      if ($tree[$key]->subtree) {
        $tree[$key]->subtree = $this->checkAccess($tree[$key]->subtree);
      }
    }
    return $tree;
  }

}
