<?php

/**
 * @file
 * Hooks provided by the Layout Builder module.
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

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Perform custom block form alterations before it's rendered.
 *
 * @param array $element
 *   The containing element.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The current state of the form.
 * @param \Drupal\block_content\BlockContentInterface $block
 *   The block content entity.
 */
function hook_layout_builder_inline_block_alter(array &$element, \Drupal\Core\Form\FormStateInterface $form_state, \Drupal\block_content\BlockContentInterface $block) {
  if ($block->bundle() !== 'awesome_block') {
    return;
  }

  $element['info']['#access'] = FALSE;
}

/**
 * Perform a block bundle specific form alterations before it's rendered.
 *
 * @param array $element
 *   The containing element.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The current state of the form.
 * @param \Drupal\block_content\BlockContentInterface $block
 *   The block content entity.
 */
function hook_layout_builder_inline_block_BLOCK_BUNDLE_alter(array &$element, \Drupal\Core\Form\FormStateInterface $form_state, \Drupal\block_content\BlockContentInterface $block) {
  $element['info']['#access'] = FALSE;
}

/**
 * @} End of "addtogroup hooks".
 */
