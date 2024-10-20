<?php

namespace Drupal\user\Hook;

use Drupal\Core\Hook\Hook;
use Drupal\views\ViewExecutable;

/**
 * Hook implementations for User module.
 */
class UserViewsExecutionHooks {

  /**
   * Implements hook_views_query_substitutions().
   *
   * Allow replacement of current user ID so we can cache these queries.
   */
  #[Hook('views_query_substitutions')]
    public function userViewsQuerySubstitutions(ViewExecutable $view) {
    return ['***CURRENT_USER***' => \Drupal::currentUser()->id()];
    }

}
