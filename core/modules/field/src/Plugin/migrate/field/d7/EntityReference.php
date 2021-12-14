<?php

namespace Drupal\field\Plugin\migrate\field\d7;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\field\FieldPluginBase;

// cspell:ignore entityreference

/**
 * MigrateField plugin for Drupal 7 entity_reference fields.
 *
 * @MigrateField(
 *   id = "entityreference",
 *   type_map = {
 *     "entityreference" = "entity_reference",
 *   },
 *   core = {7},
 *   source_module = "entityreference",
 *   destination_module = "core"
 * )
 */
class EntityReference extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldFormatterMap() {
    return [
      'entityreference_label' => 'entity_reference_label',
      'entityreference_entity_id' => 'entity_reference_entity_id',
      'entityreference_entity_view' => 'entity_reference_entity_view',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function transformFieldInstanceSettings(Row $row) {
    // Get entityreference handler settings from source field configuration.
    $field_definition = $row->get('field_definition');
    $field_data = unserialize($field_definition['data']);

    $field_settings = $field_data['settings'];
    $instance_settings['handler'] = 'default:' . $field_settings['target_type'];
    // Transform the sort settings to D8 structure.
    $sort = [
      'field' => '_none',
      'direction' => 'ASC',
    ];
    if (!empty(array_filter($field_settings['handler_settings']['sort']))) {
      if ($field_settings['handler_settings']['sort']['type'] == "property") {
        $sort = [
          'field' => $field_settings['handler_settings']['sort']['property'],
          'direction' => $field_settings['handler_settings']['sort']['direction'],
        ];
      }
      elseif ($field_settings['handler_settings']['sort']['type'] == "field") {
        $sort = [
          'field' => $field_settings['handler_settings']['sort']['field'],
          'direction' => $field_settings['handler_settings']['sort']['direction'],
        ];
      }
    }
    if (empty($field_settings['handler_settings']['target_bundles'])) {
      $field_settings['handler_settings']['target_bundles'] = NULL;
    }
    $field_settings['handler_settings']['sort'] = $sort;
    $instance_settings['handler_settings'] = $field_settings['handler_settings'];

    return $instance_settings;
  }

  /**
   * {@inheritdoc}
   */
  public function transformFieldStorageSettings(Row $row) {
    $settings['target_type'] = $row->get('settings/target_type');
    return $settings;
  }

}
