<?php

namespace Drupal\block\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Controller for building the block instance add form.
 */
class BlockAddController extends ControllerBase {

  /**
   * Build the block instance add form.
   *
   * @param string $plugin_id
   *   The plugin ID for the block instance.
   * @param string $theme
   *   The name of the theme for the block instance.
   *
   * @return array
   *   The block instance edit form.
   */
  public function blockAddConfigureForm($plugin_id, $theme) {
    // Create a block entity.
    $entity = $this->entityTypeManager()->getStorage('block')->create(['plugin' => $plugin_id, 'theme' => $theme]);

    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Checks access for the place blocks route.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to check.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The result of the access check.
   */
  public function access(AccountInterface $account) {
    return AccessResult::allowedIfHasPermissions($account, ['administer blocks', 'place blocks'], 'OR');
  }

}
