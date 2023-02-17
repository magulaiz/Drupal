<?php

/**
 * @file
 * Provide views runtime hooks for node.module.
 */

use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_query_substitutions().
 */
function node_views_query_substitutions(ViewExecutable $view) {
  $account = \Drupal::currentUser();
  return [
    '***ADMINISTER_NODES***' => (int) $account->hasPermission('administer nodes'),
    '***VIEW_OWN_UNPUBLISHED_NODES***' => (int) $account->hasPermission('view own unpublished content'),
    '***BYPASS_NODE_ACCESS***' => (int) $account->hasPermission('bypass node access'),
  ];
}
