<?php

/**
 * @file
 * Hooks provided by the Layout Builder module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provides a way to change the allowed inline blocks for a given section
 * storage, delta and region.
 *
 * @param array &$inline_blocks
 *   Array of "inline_block": bundles with the "inline_block:" prefix.
 * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
 *   The section storage.
 * @param array $context
 *   An associative array containing:
 *   - delta: The delta of the section to splice.
 *   - region: The region the block is going in.
 *
 * @see \Drupal\layout_builder\Plugin\SectionStorage\SectionStorageBase::inlineBlocksAllowedInContext()
 *
 * @ingroup layout_builder
 */
function hook_layout_builder_allowed_inline_blocks_alter(array &$inline_blocks, \Drupal\layout_builder\SectionStorageInterface $section_storage, array $context) {
  // Don't allow custom basic blocks.
  if (($index = array_search('inline_block:basic', $inline_blocks)) !== FALSE) {
    unset($inline_blocks[$index]);
  }
}

/**
 * @} End of "addtogroup hooks".
 */


/**
 * @defgroup layout_builder_access Layout Builder access
 * @{
 * In determining access rights for the Layout Builder UI,
 * \Drupal\layout_builder\Access\LayoutBuilderAccessCheck checks if the
 * specified section storage plugin (an implementation of
 * \Drupal\layout_builder\SectionStorageInterface) grants access.
 *
 * By default, the Layout Builder access check requires the 'configure any
 * layout' permission. Individual section storage plugins may override this by
 * setting the 'handles_permission_check' annotation key to TRUE. Any section
 * storage plugin that uses 'handles_permission_check' must provide its own
 * complete routing access checking to avoid any access bypasses.
 *
 * This access checking is only enforced on the routing level (not on the entity
 * or field level) with additional form access restrictions. All HTTP API access
 * to Layout Builder data is currently forbidden.
 *
 * @see https://www.drupal.org/project/drupal/issues/2942975
 */

/**
 * @} End of "defgroup layout_builder_access".
 */
