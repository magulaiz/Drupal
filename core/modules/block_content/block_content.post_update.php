<?php

/**
 * @file
 * Post update functions for Content Block.
 */

/**
 * Implements hook_removed_post_updates().
 */
function block_content_removed_post_updates(): array {
  return [
    'block_content_post_update_add_views_reusable_filter' => '9.0.0',
    'block_content_post_update_entity_changed_constraint' => '11.0.0',
    'block_content_post_update_move_custom_block_library' => '11.0.0',
    'block_content_post_update_block_library_view_permission' => '11.0.0',
    'block_content_post_update_sort_permissions' => '11.0.0',
    'block_content_post_update_revision_type' => '11.0.0',
  ];
}

/**
 * Removes the "area_text_custom" plugin from block content view configuration.
 */
function block_content_post_update_10301(?array &$sandbox = NULL): int {
  $storage = \Drupal::entityTypeManager()->getStorage('view');
  $view_ids = $storage->getQuery()->execute();

  // Initialize batch processing if first run.
  if (!isset($sandbox['total'])) {
    $sandbox['total'] = count($view_ids);
    $sandbox['current_index'] = 0;
  }

  // Process views in chunks of 10.
  $view_ids = array_slice($view_ids, $sandbox['current_index'], 10);
  foreach ($view_ids as $view_id) {
    $view = $storage->load($view_id);
    if ($view) {
      $changed = FALSE;
      foreach ($view->get('display') as $display_id => $display) {
        if (
          isset($display['display_options']['empty']['area_text_custom']) &&
          $display['display_options']['empty']['area_text_custom']['content'] === 'There are no content blocks available.'
        ) {
          unset($display['display_options']['empty']['area_text_custom']);
          $view->set('display.' . $display_id . '.display_options.empty.area_text_custom', NULL);
          $changed = TRUE;
        }
      }
      if ($changed) {
        $view->save();
      }
    }
  }

  // Update progress.
  $sandbox['current_index'] += 10;
  if ($sandbox['current_index'] < $sandbox['total']) {
    return t('Updating block content views (@current/@total)', [
      '@current' => $sandbox['current_index'],
      '@total' => $sandbox['total'],
    ]);
  }
}
