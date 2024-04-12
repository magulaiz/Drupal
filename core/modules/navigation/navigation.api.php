<?php

/**
 * @file
 * Hooks provided by the Navigation module.
 */

/**
 * Provide a navigation block plugin specific navigation_block_view alteration.
 *
 * In this hook name, BASE_NAVIGATION_BLOCK_ID refers to the navigation block
 * implementation's plugin id, regardless of whether the plugin supports
 * derivatives. For example, for the
 * \Drupal\navigation\Plugin\NavigationBlock\ContentNavigationBlock navigation
 * block, this would be 'content' as per that class's attribute.
 *
 * @param array $build
 *   A renderable array of data, as returned from the build() implementation of
 *   the plugin that defined the navigation block:
 *   - #title: The default localized title of the navigation block.
 * @param \Drupal\navigation\NavigationBlockPluginInterface $navigation_block
 *   The navigation block plugin instance.
 *
 * @see hook_navigation_block_view_alter()
 * @see entity_crud
 *
 * @ingroup navigation_block_api
 */
function hook_navigation_block_view_BASE_NAVIGATION_BLOCK_ID_alter(array &$build, \Drupal\navigation\NavigationBlockPluginInterface $navigation_block) {
  // Change the title of the specific navigation block.
  $build['#title'] = t('New title of the navigation block');
}

/**
 * Alter the result of \Drupal\navigation\NavigationBlockBase::build().
 *
 * This hook is called after the content has been assembled in a structured
 * array and may be used for doing processing which requires that the complete
 * navigation_block content structure has been built.
 *
 * If the module wishes to act on the rendered HTML of the navigation_block
 * rather than the structured content array, it may use this hook to add a
 * #post_render callback. Alternatively, it could also implement
 * hook_preprocess_HOOK() for navigation-block.html.twig. See
 * \Drupal\Core\Render\RendererInterface::render() documentation or the @link
 * themeable Default theme implementations topic @endlink for details.
 *
 * In addition to hook_navigation_block_view_alter(), which is called for all
 * navigation_blocks, there is
 * hook_navigation_block_view_BASE_NAVIGATION_BLOCK_ID_alter(), which can be
 * used to target a specific navigation block or set of similar navigation
 * blocks.
 *
 * @param array &$build
 *   A renderable array of data, as returned from the build() implementation of
 *   the plugin that defined the navigation block:
 *   - #title: The default localized title of the navigation block.
 * @param \Drupal\navigation\NavigationBlockPluginInterface $navigation_block
 *   The navigation block plugin instance.
 *
 * @see hook_navigation_block_view_BASE_NAVIGATION_BLOCK_ID_alter()
 * @see entity_crud
 *
 * @ingroup navigation_block_api
 */
function hook_navigation_block_view_alter(array &$build, \Drupal\navigation\NavigationBlockPluginInterface $navigation_block) {
  // Remove the contextual links on all blocks that provide them.
  if (isset($build['#contextual_links'])) {
    unset($build['#contextual_links']);
  }
}
