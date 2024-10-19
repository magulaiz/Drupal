<?php

/**
 * @file
 * Provide views runtime hooks for user.module.
 */

use Drupal\Core\Hook\LegacyHook;
use Drupal\user\Hook\UserViewsExecutionHooks;
use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_query_substitutions().
 *
 * Allow replacement of current user ID so we can cache these queries.
 */
#[LegacyHook]
function user_views_query_substitutions(ViewExecutable $view) {
  \Drupal::service(UserViewsExecutionHooks::CLASS)->userViewsQuerySubstitutions($view);
}
