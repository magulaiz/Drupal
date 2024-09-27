<?php

namespace Drupal\user;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;

/**
 * Defines an interface for role entity storage classes.
 */
interface RoleStorageInterface extends ConfigEntityStorageInterface {

  /**
   * Returns whether a permission is in one of the passed in roles.
   *
   *
   * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. There is no
   *   replacement.
   * @see https://www.drupal.org/node/3477235
   *
   * @param string $permission
   *   The permission.
   * @param array $rids
   *   The list of role IDs to check.
   *
   * @return bool
   *   TRUE is the permission is in at least one of the roles. FALSE otherwise.
   */
  public function isPermissionInRoles($permission, array $rids);

}
