<?php

/**
 * @file
 * Post update functions for Migrate Drupal.
 */

/**
 * Implements hook_post_update_last_removed().
 */
function migrate_drupal_post_update_last_removed() {
  return [
    'migrate_drupal_post_update_uninstall_multilingual' => '10.0.0',
  ];
}
