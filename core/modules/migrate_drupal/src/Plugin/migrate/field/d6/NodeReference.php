<?php

namespace Drupal\migrate_drupal\Plugin\migrate\field\d6;

use Drupal\migrate_drupal\Plugin\migrate\field\ReferenceBase;
use Drupal\migrate\Row;

/**
 * MigrateField Plugin for Drupal 6 node reference fields.
 *
 * @MigrateField(
 *   id = "nodereference",
 *   core = {6},
 *   type_map = {
 *     "nodereference" = "entity_reference",
 *   },
 *   source_module = "nodereference",
 *   destination_module = "core",
 * )
 *
 * @internal
 */
class NodeReference extends ReferenceBase {

  /**
   * The plugin ID for the reference type migration.
   *
   * @var string
   */
  protected $nodeTypeMigration = 'd6_node_type';

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeMigrationId() {
    return $this->nodeTypeMigration;
  }

  /**
   * {@inheritdoc}
   */
  protected function entityId() {
    return 'nid';
  }

  /**
   * {@inheritdoc}
   */
  public function transformFieldInstanceSettings(Row $row) {
    $source_settings = $row->getSourceProperty('global_settings');
    $settings['handler'] = 'default:node';
    $settings['handler_settings']['target_bundles'] = [];

    if (isset($source_settings['referenceable_types'])) {
      $node_types = array_filter($source_settings['referenceable_types']);
      if (!empty($node_types)) {
        $settings['handler_settings']['target_bundles'] = $this->lookupMigrations('d6_node_type', $node_types);
      }
    }
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function transformFieldStorageSettings(Row $row) {
    $settings['target_type'] = 'node';
    return $settings;
  }

}
