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
 * Add 'base_route_title' setting for page title blocks.
 */
function block_post_update_add_base_route_title_page_title() {
  $config_factory = \Drupal::configFactory();
  foreach ($config_factory->listAll('block.block.') as $block_config_name) {
    if (!str_contains($block_config_name, 'page_title')) {
      continue;
    }
    $block = $config_factory->getEditable($block_config_name);

    $settings = $block->get('settings');
    if (str_contains($block->getName(), 'claro')) {
      $settings['base_route_title'] = TRUE;
    }
    else {
      $settings['base_route_title'] = FALSE;
    }
    $block->set('settings', $settings);

    // Mark the resulting configuration as trusted data. This avoids issues with
    // future schema changes.
    $block->save(TRUE);
  }
}
