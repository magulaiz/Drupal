<?php

/**
 * @file
 * Post update functions for Node.
 */

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\node\Entity\NodeType;

/**
 * Implements hook_removed_post_updates().
 */
function node_removed_post_updates(): array {
  return [
    'node_post_update_configure_status_field_widget' => '9.0.0',
    'node_post_update_node_revision_views_data' => '9.0.0',
    'node_post_update_glossary_view_published' => '10.0.0',
    'node_post_update_rebuild_node_revision_routes' => '10.0.0',
    'node_post_update_modify_base_field_author_override' => '10.0.0',
    'node_post_update_set_node_type_description_and_help_to_null' => '11.0.0',
  ];
}

/**
 * Creates base field override config for the promote base field on node types.
 *
 * @todo Decide if we need to batch this.
 */
function node_post_update_create_promote_base_field_overrides(): void {
  /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager */
  $entityFieldManager = \Drupal::service(EntityFieldManagerInterface::class);
  $promoteFieldDefinition = $entityFieldManager->getBaseFieldDefinitions('node')['promote'];
  foreach (NodeType::loadMultiple() as $nodeType) {
    $config = $promoteFieldDefinition->getConfig($nodeType->id());
    // Don't change existing base_field_override configuration.
    if (!$config->isNew()) {
      continue;
    }
    // Set the default value for existing node types that didn't already have
    // a base_field_override to TRUE, maintaining the previous default.
    $config->setDefaultValue(TRUE)->save();
  }
}
