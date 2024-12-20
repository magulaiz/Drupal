<?php

/**
 * @file
 * Post update functions for Migrate Drupal.
 */

declare(strict_types=1);

/**
 * Implements hook_removed_post_updates().
 */
function migrate_drupal_removed_post_updates(): array {
  return [
    'migrate_drupal_post_update_uninstall_multilingual' => '10.0.0',
  ];
}
