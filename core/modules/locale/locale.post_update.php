<?php

/**
 * @file
 * Post-update functions for Locale module.
 */

declare(strict_types=1);

/**
 * Implements hook_removed_post_updates().
 */
function locale_removed_post_updates(): array {
  return [
    'locale_post_update_clear_cache_for_old_translations' => '9.0.0',
  ];
}
