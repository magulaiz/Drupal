<?php

/**
 * @file
 * Post update functions for the Database Logging module.
 */

/**
 * Implements hook_removed_post_updates().
 */
function dblog_removed_post_updates() {
  return [
    'dblog_post_update_convert_recent_messages_to_view' => '9.0.0',
    'dblog_post_update_add_langcode_to_settings' => '11.0.0',
  ];
}
