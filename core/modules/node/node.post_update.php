<?php

/**
 * @file
 * Post update functions for Node.
 */

<<<<<<< HEAD
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\views\Entity\View;
=======
use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\node\NodeTypeInterface;

/**
 * Converts empty `description` and `help` in content types to NULL.
 */
function node_post_update_set_node_type_description_and_help_to_null(array &$sandbox): void {
  \Drupal::classResolver(ConfigEntityUpdater::class)
    ->update($sandbox, 'node_type', function (NodeTypeInterface $node_type): bool {
      // Content types' `help` and `description` fields must be stored as NULL
      // at the config level if they are empty.
      if (trim($node_type->getDescription()) === '') {
        $node_type->set('description', NULL);
      }
      if (trim($node_type->getHelp()) === '') {
        $node_type->set('help', NULL);
      }
      return TRUE;
    });
}
>>>>>>> upstream/11.x

/**
 * Implements hook_removed_post_updates().
 */
function node_removed_post_updates() {
  return [
    'node_post_update_configure_status_field_widget' => '9.0.0',
    'node_post_update_node_revision_views_data' => '9.0.0',
    'node_post_update_glossary_view_published' => '10.0.0',
    'node_post_update_rebuild_node_revision_routes' => '10.0.0',
    'node_post_update_modify_base_field_author_override' => '10.0.0',
  ];
}
<<<<<<< HEAD

/**
 * Add a published filter to the glossary View.
 */
function node_post_update_glossary_view_published() {
  if (\Drupal::moduleHandler()->moduleExists('views')) {
    $view = View::load('glossary');
    if (!$view) {
      return;
    }
    $display =& $view->getDisplay('default');
    if (!isset($display['display_options']['filters']['status'])) {
      $display['display_options']['filters']['status'] = [
        'expose' => [
          'operator' => '',
          'operator_limit_selection' => FALSE,
          'operator_list' => [],
        ],
        'field' => 'status',
        'group' => 1,
        'id' => 'status',
        'table' => 'node_field_data',
        'value' => '1',
        'plugin_id' => 'boolean',
        'entity_type' => 'node',
        'entity_field' => 'status',
      ];
      $view->save();
    }
  }
}

/**
 * Rebuild the node revision routes.
 */
function node_post_update_rebuild_node_revision_routes() {
  // Empty update to rebuild routes.
}

/**
 * Updates stale references to Drupal\node\Entity\Node::getCurrentUserId.
 */
function node_post_update_modify_base_field_author_override() {
  $uid_fields = \Drupal::entityTypeManager()
    ->getStorage('base_field_override')
    ->getQuery()
    ->condition('entity_type', 'node')
    ->condition('field_name', 'uid')
    ->condition('default_value_callback', 'Drupal\node\Entity\Node::getCurrentUserId')
    ->execute();
  foreach (BaseFieldOverride::loadMultiple($uid_fields) as $base_field_override) {
    $base_field_override->setDefaultValueCallback('Drupal\node\Entity\Node::getDefaultEntityOwner')->save();
  }
}
=======
>>>>>>> upstream/11.x
