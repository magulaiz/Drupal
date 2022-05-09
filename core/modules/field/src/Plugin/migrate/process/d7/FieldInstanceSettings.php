<?php

namespace Drupal\field\Plugin\migrate\process\d7;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

// cspell:ignore entityreference

/**
 * @MigrateProcessPlugin(
 *   id = "d7_field_instance_settings"
 * )
 */
class FieldInstanceSettings extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The field plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $fieldPluginManager;

  /**
   * Constructs a FieldSettings plugin.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $field_plugin_manager
   *   The field plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginManagerInterface $field_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fieldPluginManager = $field_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.migrate.field')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $original_field_type = $row->getSourceProperty('type');
    if ($original_field_type == 'text') {
      $original_field_type = 'd7_' . $original_field_type;
    }
    try {
      return $this->fieldPluginManager->createInstance($original_field_type, ['core' => 7])
        ->transformFieldInstanceSettings($row);
    }
    catch (PluginNotFoundException $e) {
    }

    [$instance_settings, $widget_settings, $field_definition] = $value;
    $widget_type = $widget_settings['type'];

    $field_data = unserialize($field_definition['data']);

    // Get the labels for the list_boolean type.
    if ($row->getSourceProperty('type') === 'list_boolean') {
      if (isset($field_data['settings']['allowed_values'][1])) {
        $instance_settings['on_label'] = $field_data['settings']['allowed_values'][1];
      }
      if (isset($field_data['settings']['allowed_values'][0])) {
        $instance_settings['off_label'] = $field_data['settings']['allowed_values'][0];
      }
    }

    switch ($widget_type) {
      case 'image_image':
      case 'image_miw':
        $settings = $instance_settings;
        $settings['default_image'] = [
          'alt' => '',
          'title' => '',
          'width' => NULL,
          'height' => NULL,
          'uuid' => '',
        ];
        break;

      default:
        $settings = $instance_settings;
    }

    return $settings;
  }

}
