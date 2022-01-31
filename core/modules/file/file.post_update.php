<?php

/**
 * @file
 * Post update functions for File.
 */

/**
 * Implements hook_post_update_last_removed().
 */
function file_post_update_last_removed() {
  return [
    'file_post_update_add_txt_if_allows_insecure_extensions' => '10.0.0',
  ];
}
