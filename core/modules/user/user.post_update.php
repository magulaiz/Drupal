<?php

/**
 * @file
 * Post update functions for User module.
 */

use Drupal\system\Entity\Action;
use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\user\Entity\Role;

/**
 * Implements hook_removed_post_updates().
 */
function user_removed_post_updates(): array {
  return [
    'user_post_update_enforce_order_of_permissions' => '9.0.0',
    'user_post_update_update_roles' => '10.0.0',
    'user_post_update_sort_permissions' => '11.0.0',
    'user_post_update_sort_permissions_again' => '11.0.0',
  ];
}

/**
 * Ensure permissions stored in role configuration are sorted using the schema.
 */
function user_post_update_sort_permissions(&$sandbox = NULL) {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'user_role', function (Role $role) {
    $permissions = $role->getPermissions();
    sort($permissions);
    return $permissions !== $role->getPermissions();
  });
}

/**
 * Add an action to send activation emails to multiple users.
 */
function user_post_update_add_send_action() {
  $action = Action::create([
    'id' => 'user_welcome_message_action',
    'type' => 'user',
    'label' => 'Send the welcome message to the selected user(s)',
    'configuration' => [],
    'plugin' => 'user_welcome_message_action',
  ]);
  $action->trustData()->save();
}
