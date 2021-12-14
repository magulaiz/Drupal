<?php

namespace Drupal\file\Plugin\migrate\field\d7;

use Drupal\file\Plugin\migrate\field\d6\FileField as D6FileField;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;

// cspell:ignore imagefield

// cspell:ignore filefield

/**
 * @MigrateField(
 *   id = "file",
 *   core = {7},
 *   source_module = "file",
 *   destination_module = "file"
 * )
 */
class FileField extends D6FileField {

  /**
   * {@inheritdoc}
   */
  public function getFieldWidgetMap() {
    return [
      'file_mfw' => 'file_generic',
      'filefield_widget' => 'file_generic',
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
        'target_id' => 'fid',
        'display' => 'display',
        'description' => 'description',
      ],
    ];
    $migration->mergeProcessOfProperty($field_name, $process);
  }

  /**
   * {@inheritdoc}
   */
  public function transformFieldInstanceSettings(Row $row) {
    $widget_type = $row->getSourceProperty('widget_type');
    $widget_settings = $row->getSourceProperty('widget_settings');
    $field_settings = $row->getSourceProperty('global_settings');
    $settings = [];

    switch ($widget_type) {
      case 'filefield_widget':
        $settings['file_extensions'] = $widget_settings['file_extensions'];
        $settings['file_directory'] = $widget_settings['file_path'];
        $settings['description_field'] = $field_settings['description_field'];
        $settings['max_filesize'] = $this->convertSizeUnit($widget_settings['max_filesize_per_file']);
        break;

      case 'imagefield_widget':
        $settings['file_extensions'] = $widget_settings['file_extensions'];
        $settings['file_directory'] = 'public://';
        $settings['max_filesize'] = $this->convertSizeUnit($widget_settings['max_filesize_per_file']);
        $settings['alt_field'] = $widget_settings['alt'];
        $settings['alt_field_required'] = $widget_settings['custom_alt'];
        $settings['title_field'] = $widget_settings['title'];
        $settings['title_field_required'] = $widget_settings['custom_title'];
        $settings['max_resolution'] = $widget_settings['max_resolution'];
        $settings['min_resolution'] = $widget_settings['min_resolution'];
        break;
    }

    return $settings;
  }

}
