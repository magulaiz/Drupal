<?php

/**
 * @file
 * Post update functions for Custom Block.
 */

use Drupal\views\Entity\View;

/**
 * Implements hook_removed_post_updates().
 */
function block_content_removed_post_updates() {
  return [
    'block_content_post_update_add_views_reusable_filter' => '9.0.0',
  ];
}

/**
 * Clear the entity type cache.
 */
function block_content_post_update_entity_changed_constraint() {
  // Empty post_update hook.
}

/**
 * Moves the custom block library to Content.
 */
function block_content_post_update_move_custom_block_library() {
  if (Drupal::service('module_handler')->moduleExists('views')) {
    if ($view = View::load('block_content')) {
      $view_updated = FALSE;

      $display =& $view->getDisplay('page_1');
      if (!empty($display) && $display['display_options']['path'] !== 'admin/content/block-content') {
        $display['display_options']['path'] = 'admin/content/block-content';
        $menu =& $display['display_options']['menu'];
        $menu['description'] = 'Create and edit custom block content.';
        $menu['expanded'] = FALSE;
        $menu['parent'] = 'system.admin_content';
        $view_updated = TRUE;
      }

      $display =& $view->getDisplay('default');
      if (!empty($display)) {
        $display['display_options']['empty']['area_text_custom']['content'] = 'No custom blocks available.';
        $view_updated = TRUE;
      }

      if ($view_updated) {
        $view->save();
      }
    }
  }
}
