<?php

namespace Drupal\block\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Implements access checks for block admin routes.
 */
class BlockAdminAccessCheck implements AccessInterface {

  /**
   * Check if the current user has permission to manage blocks.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user to check.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {
    return AccessResult::allowedIfHasPermissions($account, ['administer blocks', 'access block overview'], 'OR')->cachePerPermissions();
  }

}
