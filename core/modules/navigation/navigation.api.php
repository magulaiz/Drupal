<?php

/**
 * @file
 * Hooks related to the Navigation module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Lists the blocks intended to be used in the Navigation.
 *
 * @return array
 *   An array of block ids.
 *
 * @see hook_navigation_block_alter()
 */
function hook_navigation_block(): array {
  return [
    'navigation_user',
    'navigation_shortcuts',
    'navigation_menu',
  ];
}

/**
 * Alters the list of blocks intended to be used in the Navigations.
 *
 * @param $navigation_blocks
 *   An array of navigation safe blocks returned by hook_navigation_promoted().
 *
 * @see hook_navigation_block()
 */
function hook_navigation_block_alter(array &$navigation_blocks): void {
  // Remove a specific block.
  unset($navigation_blocks['hook_navigation_block_alter']);
  // Add a specific block if my_module is enabled.
  if (\Drupal::moduleHandler()->moduleExists('my_module')) {
    $navigation_blocks[] = 'new_navigation_safe_block';
  }
}

/**
 * @} End of "addtogroup hooks".
 */
