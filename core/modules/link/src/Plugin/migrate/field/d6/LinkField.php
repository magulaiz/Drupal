<?php

namespace Drupal\link\Plugin\migrate\field\d6;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\field\FieldPluginBase;

/**
 * @MigrateField(
 *   id = "link",
 *   core = {6},
 *   type_map = {
 *     "link" = "link",
 *   },
 *   source_module = "link",
 *   destination_module = "link"
 * )
 */
class LinkField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldFormatterMap() {
    // See d6_field_formatter_settings.yml and FieldPluginBase
    // alterFieldFormatterMigration().
    return [
      'default' => 'link',
      'plain' => 'link',
      'absolute' => 'link',
      'title_plain' => 'link',
      'url' => 'link',
      'short' => 'link',
      'label' => 'link',
      'separate' => 'link_separate',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defineValueProcessPipeline(MigrationInterface $migration, $field_name, $data) {
    $process = [
      'plugin' => 'field_link',
      'source' => $field_name,
    ];
    $migration->mergeProcessOfProperty($field_name, $process);
  }

  /**
   * {@inheritdoc}
   */
  public function transformFieldInstanceSettings(Row $row) {
    $field_settings = $row->getSourceProperty('global_settings');
    if (isset($field_settings['title'])) {
      // D6 has optional, required, value and none. D8 only has disabled
      // (DRUPAL_DISABLED), optional (DRUPAL_OPTIONAL) and required
      // (DRUPAL_REQUIRED).
      $map = [
        'disabled' => 0,
        'optional' => 1,
        'required' => 2,
      ];
      $settings['title'] = $map[$field_settings['title']];
    }
    else {
      // In case we are missing title in field settings, use disabled value
      // "DRUPAL_DISABLED" as a default value.
      $settings['title'] = 0;
    }
    return $settings;
  }

}
