<?php

namespace Drupal\Core\Session;

/**
 * Defines a permission checker interface.
 *
 * @ingroup user_api
 */
interface PermissionCheckerInterface {

  /**
   * Checks whether an account has a permission.
   *
   * @param string $permission
   *   The name of the permission to check for.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account for which to check the permissions.
   *
   * @return bool
   *   Whether the account has the permission.
   */
  public function hasPermission(string $permission, AccountInterface $account): bool;

}
