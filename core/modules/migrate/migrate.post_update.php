<?php

/**
 * @file
 * Post update functions for migrate.
 */

/**
 * Implements hook_post_update_last_removed().
 */
function migrate_post_update_last_removed() {
  return [
    'migrate_post_update_clear_migrate_source_count_cache' => '10.0.0',
  ];
}
