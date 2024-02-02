<?php

/**
 * @file
 * Post update functions for Update Manager.
 */

/**
<<<<<<< HEAD
 * Add 'view update notifications' to roles with 'administer site configuration'.
 */
function update_post_update_add_view_update_notifications_permission(&$sandbox) {
  $roles = user_roles(FALSE, 'administer site configuration');
  foreach ($roles as $role) {
    $role->grantPermission('view update notifications')->save();
=======
 * Implements hook_removed_post_updates().
 */
function update_remove_post_updates() {
  return [
    'update_post_update_add_view_update_notifications_permission' => '10.0.0',
  ];
}

/**
 * Updates update.settings:fetch.url config if it's still at the default.
 */
function update_post_update_set_blank_fetch_url_to_null() {
  $update_settings = \Drupal::configFactory()->getEditable('update.settings');
  if ($update_settings->get('fetch.url') === '') {
    $update_settings
      ->set('fetch.url', NULL)
      ->save(TRUE);
>>>>>>> upstream/11.x
  }
}
