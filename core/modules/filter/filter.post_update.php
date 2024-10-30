<?php

/**
 * @file
 * Post update functions for Filter.
 */

declare(strict_types=1);

/**
 * Implements hook_removed_post_updates().
 */
function filter_removed_post_updates(): array {
  return [
    'filter_post_update_sort_filters' => '11.0.0',
    'filter_post_update_consolidate_filter_config' => '11.0.0',
  ];
}
