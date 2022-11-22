<?php

/**
 * @file
 * Provides views runtime hooks for forum.module.
 */

/**
 * Implements hook_preprocess_HOOK().
 */
function forum_preprocess_views_more(&$variables) {
  /** @var \Drupal\views\ViewExecutable $view */
  $view = $variables['view'];
  if ($view->storage->id() == 'forum_topic_lists') {
    // Append some descriptive text to provide context to the 'Read More' link.
    $variables['attributes']['title'] = $view->getTitle();
  }
}
