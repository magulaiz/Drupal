<?php

/**
 * @file
 * Post update functions for the Navigation module.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\user\RoleInterface;

/**
 * Grants navigation specific permission to roles with access to any layout.
 */
function navigation_post_update_update_permissions(array &$sandbox) {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'user_role', function (RoleInterface $role) {
    if ($role->hasPermission('configure any layout')) {
      $role->grantPermission('configure navigation layout');
      return TRUE;
    }
    return FALSE;
  });
}
