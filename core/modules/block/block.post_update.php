<?php

/**
 * @file
 * Post update functions for Block.
 */

/**
 * Implements hook_removed_post_updates().
 */
function block_removed_post_updates() {
  return [
    'block_post_update_disable_blocks_with_missing_contexts' => '9.0.0',
    'block_post_update_disabled_region_update' => '9.0.0',
    'block_post_update_fix_negate_in_conditions' => '9.0.0',
    'block_post_update_replace_node_type_condition' => '10.0.0',
  ];
}

/**
 * Adds config dependency for site branding block on 'system.site'.
 */
function block_post_update_add_dependency_to_branding_block(array &$sandbox = NULL) {
  $site_branding_settings = \Drupal::configFactory()->getEditable('block.block.site_branding');
  $dependencies = $site_branding_settings->get('dependencies');
  $dependencies['config'][] = ['system.site'];
  $site_branding_settings
    ->set('dependencies', $dependencies)
    ->save(TRUE);
}
