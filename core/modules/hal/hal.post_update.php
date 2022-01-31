<?php

/**
 * @file
 * Post update functions for Hal.
 */

/**
 * Implements hook_post_update_last_removed().
 */
function hal_post_update_last_removed() {
  return [
    'hal_post_update_delete_settings' => '10.0.0',
  ];
}
