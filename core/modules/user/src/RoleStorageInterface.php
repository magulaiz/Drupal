<?php

namespace Drupal\user;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;

/**
 * Defines an interface for role entity storage classes.
 *
 * @method RoleInterface create(array $values = [])
 * @method null|RoleInterface load($id)
 * @method null|RoleInterface loadRevision($revision_id)
 * @method null|RoleInterface loadUnchanged($id)
 * @method RoleInterface[] loadMultiple(array $ids = NULL)
 * @method RoleInterface[] loadByProperties(array $values = [])
 * @method null|int save(RoleInterface $entity)
 * @method void restore(RoleInterface $entity)
 */
interface RoleStorageInterface extends ConfigEntityStorageInterface {

  /**
   * Returns whether a permission is in one of the passed in roles.
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
