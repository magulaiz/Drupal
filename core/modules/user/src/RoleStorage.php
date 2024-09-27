<?php

namespace Drupal\user;

use Drupal\Core\Config\Entity\ConfigEntityStorage;

/**
 * Controller class for user roles.
 */
class RoleStorage extends ConfigEntityStorage implements RoleStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function isPermissionInRoles($permission, array $rids) {
    @trigger_error(__METHOD__ . '() is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. There is no replacement. See https://www.drupal.org/node/3477235', E_USER_DEPRECATED);
    foreach ($this->loadMultiple($rids) as $role) {
      /** @var \Drupal\user\RoleInterface $role */
      if ($role->hasPermission($permission)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
