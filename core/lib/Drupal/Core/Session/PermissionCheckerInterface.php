<?php

namespace Drupal\Core\Session;

/**
 * Defines a permission checker interface.
 *
 * This service can be swapped out or decorated by contrib modules to enable
 * access logic more complex than simply checking permissions on roles. Please
 * be careful when changing how this service works and provide ample automated
 * tests when doing so as you may open your website up to security issues.
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
