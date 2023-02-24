<?php

/**
 * @file
 * Post update functions for Help.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\user\RoleInterface;

/**
 * Grant all admin roles the 'access help pages' permission.
 */
function help_post_update_add_permissions_to_roles(?array &$sandbox = NULL): void {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'user_role', function (RoleInterface $role): bool {
    if ($role->isAdmin() || !$role->hasPermission('access administration pages')) {
      return FALSE;
    }
    $role->grantPermission('access help pages');
    return TRUE;
  });
}
