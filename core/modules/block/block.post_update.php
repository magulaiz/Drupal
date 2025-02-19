<?php

/**
 * @file
 * Post update functions for Block.
 */

use Drupal\block\BlockInterface;
use Drupal\Core\Config\Entity\ConfigEntityUpdater;

/**
 * Implements hook_removed_post_updates().
 */
function block_removed_post_updates(): array {
  return [
    'block_post_update_disable_blocks_with_missing_contexts' => '9.0.0',
    'block_post_update_disabled_region_update' => '9.0.0',
    'block_post_update_fix_negate_in_conditions' => '9.0.0',
    'block_post_update_replace_node_type_condition' => '10.0.0',
  ];
}

/**
 * Ensures that all block weights are integers.
 */
function block_post_update_make_weight_integer(array &$sandbox = []): void {
  \Drupal::classResolver(ConfigEntityUpdater::class)
    ->update($sandbox, 'block', function (BlockInterface $block): bool {
      $weight = $block->getWeight();
      if (!is_int($weight)) {
        $block->setWeight($weight);
        return TRUE;
      }
      return FALSE;
    });
}

/**
 * Updates all blocks with new settings for condition logic.
 */
function block_post_update_move_custom_block_library(&$sandbox = NULL): void {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'block', function (BlockInterface $block): bool {
    $settings = $block->get('settings');
    if (\array_key_exists('condition_logic', $settings) === FALSE && $block->uuid() !== NULL) {
      $settings['condition_logic'] = 'and';
      $block->set('settings', $settings);
      return TRUE;
    }
    return FALSE;
  });
}

/**
 * Updates search blocks with default page_id value.
 */
function block_post_update_update_page_id(&$sandbox = NULL): void {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'block', function (BlockInterface $block): bool {
    $settings = $block->get('settings');
    if (\array_key_exists('page_id', $settings) === TRUE && $block->uuid() !== NULL) {
      $settings['page_id'] = ($settings['page_id'] !== '') ? $settings['page_id'] : NULL;
      $block->set('settings', $settings);
      return TRUE;
    }
    return FALSE;
  });
}
