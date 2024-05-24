<?php

/**
 * @file
 * Provide views runtime hooks for node.module.
 */

use Drupal\node\NodeInterface;
use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_query_substitutions().
 */
function node_views_query_substitutions(ViewExecutable $view) {
  $account = \Drupal::currentUser();

  $substitutions = [
    '***ADMINISTER_NODES***' => intval($account->hasPermission('administer nodes')),
    '***VIEW_OWN_UNPUBLISHED_NODES***' => intval($account->hasPermission('view own unpublished content')),
    '***BYPASS_NODE_ACCESS***' => intval($account->hasPermission('bypass node access')),
  ];

  if (\Drupal::moduleHandler()->hasImplementations('node_grants')) {
    $unpublished_nids_with_view_access = &drupal_static(__FUNCTION__, []);
    if (!array_key_exists($account->id(), $unpublished_nids_with_view_access)) {
      $unpublished_nids_with_view_access[$account->id()] = \Drupal::database()->select('node_field_data', 'n')
        ->fields('n', ['nid'])
        ->condition('n.status', NodeInterface::NOT_PUBLISHED)
        ->addTag('node_access')
        ->execute()->fetchCol();
    }
    $substitutions['***UNPUBLISHED_NIDS_ACCESS_GRANTED_BY_NODE_ACCESS***'] = $unpublished_nids_with_view_access[$account->id()] === [] ? \Drupal::database()->quote('0') : implode(', ', array_map(static fn(string $item): string => \Drupal::database()->quote($item), $unpublished_nids_with_view_access[$account->id()]));
  }
  else {
    $substitutions['***UNPUBLISHED_NIDS_ACCESS_GRANTED_BY_NODE_ACCESS***'] = \Drupal::database()->quote('0');
  }

  return $substitutions;
}
