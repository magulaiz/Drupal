<?php

/**
 * @file
 * Post update functions for Serialization module.
 */

/**
 * Implements hook_post_update_last_removed().
 */
function serialization_post_update_last_removed() {
  return [
    'serialization_post_update_delete_settings' => '10.0.0',
  ];
}
