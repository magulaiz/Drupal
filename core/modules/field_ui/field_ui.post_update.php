<?php

/**
 * @file
 * Post update functions for the field_ui module.
 */

/**
 * Fix field_ui.settings:field_prefix value as per validation constraint.
 */
function field_ui_post_update_set_field_prefix_to_thirty_characters(): void {
  $config = \Drupal::configFactory()->getEditable('field_ui.settings');
  $prefix = $config->get('field_prefix');
  if (strlen($prefix) > 30) {
    $prefix = substr($prefix, 0, 30);
    $config->set('field_prefix', $prefix)->save();
  }
}
