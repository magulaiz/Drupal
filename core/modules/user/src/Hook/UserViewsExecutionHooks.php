<?php

namespace Drupal\user\Hook;

/**
 * @file
 * Provide views runtime hooks for user.module.
 */
/**
 * @file
 * Provide views runtime hooks for user.module.
 */
use Drupal\views\ViewExecutable;
use Drupal\Core\Hook\Attribute\Hook;

class UserViewsExecutionHooks {

  /**
   * Implements hook_views_query_substitutions().
   *
   * Allow replacement of current user ID so we can cache these queries.
   */
  #[Hook('views_query_substitutions')]
  public function viewsQuerySubstitutions(ViewExecutable $view) {
    return ['***CURRENT_USER***' => \Drupal::currentUser()->id()];
  }

}
