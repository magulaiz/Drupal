<?php

/**
 * @file
 * Post update functions for the path module.
 */

declare(strict_types=1);

/**
 * Implements hook_removed_post_updates().
 */
function path_removed_post_updates(): array {
  return [
    'path_post_update_create_language_content_settings' => '9.0.0',
  ];
}
