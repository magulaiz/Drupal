<?php

/**
 * @file
 * Post update functions for Update Manager.
 */

/**
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
  }
}

/**
 * Updates update.settings:notification.emails to null if it is an empty array.
 */
function update_post_update_empty_notification_emails_to_null(): void {
  $update_settings = \Drupal::configFactory()->getEditable('update.settings');
  if (empty($update_settings->get('notification.emails'))) {
    $update_settings
      ->set('notification.emails', NULL)
      ->save();
  }
}
