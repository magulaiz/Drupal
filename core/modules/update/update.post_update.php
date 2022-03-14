<?php

/**
 * @file
 * Post update functions for Update Manager.
 */

/**
 * Add 'see update notifications' to roles with 'administer site configuration'.
 */
function update_post_update_add_see_update_notifications_permission(&$sandbox) {
  $roles = user_roles(FALSE, 'administer site configuration');
  foreach ($roles as $role) {
    $role->grantPermission('see update notifications')->save();
  }
}
