<?php

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\migrate\Row;

/**
 * Provides entity view mode destination plugin.
 *
 * See EntityConfigBase for the available configuration options.
 * @see \Drupal\migrate\Plugin\migrate\destination\EntityConfigBase
 *
 * Example:
 *
 * @code
 * source:
 *   plugin: d7_view_mode
 * process:
 *   mode: view_mode
 *   label: view_mode
 *   targetEntityType: entity_type
 * destination:
 *   plugin: entity:entity_view_mode
 * @endcode
 *
 * This will add the results of the process ("mode", "label" and
 * "targetEntityType") to an "entity_view_mode" entity.
 *
 * @MigrateDestination(
 *   id = "entity:entity_view_mode"
 * )
 */
class EntityViewMode extends EntityConfigBase {

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['targetEntityType']['type'] = 'string';
    $ids['mode']['type'] = 'string';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function rollback(array $destination_identifier) {
    $destination_identifier = implode('.', $destination_identifier);
    parent::rollback([$destination_identifier]);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity(Row $row, array $old_destination_id_values) {
    // The parent::getEntity() method uses the first part of the id to load the
    // destination entity.
    if ($old_destination_id_values[1] ?? NULL) {
      [$entity_type, $mode] = $old_destination_id_values;
      $old_destination_id_values = ["$entity_type.$mode"];
    }
    return parent::getEntity($row, $old_destination_id_values);
  }

}
