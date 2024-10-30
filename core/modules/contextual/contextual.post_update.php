<?php

/**
 * @file
 * Post update functions for Contextual Links.
 */

declare(strict_types=1);

/**
 * Implements hook_removed_post_updates().
 */
function contextual_removed_post_updates(): array {
  return [
    'contextual_post_update_fixed_endpoint_and_markup' => '9.0.0',
  ];
}
