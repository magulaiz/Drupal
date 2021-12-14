<?php

namespace Drupal\taxonomy\Plugin\migrate\field;

use Drupal\migrate_drupal\Plugin\migrate\field\ReferenceBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;

// cspeLL:ignore entityreference

/**
 * @MigrateField(
 *   id = "taxonomy_term_reference",
 *   type_map = {
 *     "taxonomy_term_reference" = "entity_reference"
 *   },
 *   core = {6,7},
 *   source_module = "taxonomy",
 *   destination_module = "core",
 * )
 */
class TaxonomyTermReference extends ReferenceBase {

  /**
   * The plugin ID for the reference type migration.
   *
   * @var string
   */
  protected $taxonomyTypeMigration = 'd7_vocabulary';

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeMigrationId() {
    return $this->taxonomyTypeMigration;
  }

  /**
   * {@inheritdoc}
   */
  protected function entityId() {
    return 'tid';
  }

  /**
   * {@inheritdoc}
   */
  public function transformFieldStorageSettings(Row $row) {
    $settings['target_type'] = 'taxonomy_term';
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldFormatterMap() {
    return [
      'taxonomy_term_reference_link' => 'entity_reference_label',
      'taxonomy_term_reference_plain' => 'entity_reference_label',
      'taxonomy_term_reference_rss_category' => 'entity_reference_label',
      'i18n_taxonomy_term_reference_link' => 'entity_reference_label',
      'entityreference_entity_view' => 'entity_reference_entity_view',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defineValueProcessPipeline(MigrationInterface $migration, $field_name, $data) {
    $process = [
      'plugin' => 'sub_process',
      'source' => $field_name,
      'process' => [
        'target_id' => 'tid',
      ],
    ];
    $migration->setProcessOfProperty($field_name, $process);
  }

  /**
   * {@inheritdoc}
   */
  public function transformFieldInstanceSettings(Row $row) {
    $instance_settings['handler_settings']['sort'] = [
      'field' => '_none',
    ];
    $allowed_values = $row->get('@allowed_values');
    foreach ($allowed_values as $allowed_value) {
      foreach ($allowed_value as $vocabulary) {
        $instance_settings['handler_settings']['target_bundles'][$vocabulary] = $vocabulary;
      }
    }
    return $instance_settings;
  }

}
