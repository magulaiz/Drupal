<?php

namespace Drupal\field\Plugin\migrate\process\d6;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

// cspell:ignore filefield imagefield

/**
 * @MigrateProcessPlugin(
 *   id = "d6_field_field_settings"
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
   *
   * Set the field instance defaults.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $original_field_type = $row->getSourceProperty('type');
    if ($original_field_type == 'text') {
      $original_field_type = 'd6_' . $original_field_type;
    }
    try {
      return $this->fieldPluginManager->createInstance($original_field_type, ['core' => 6])
        ->transformFieldInstanceSettings($row);
    }
    catch (PluginNotFoundException $e) {
    }

    [$widget_type, $widget_settings, $field_settings] = $value;
    $settings = [];
    switch ($widget_type) {
      case 'number':
        $settings['min'] = $field_settings['min'];
        $settings['max'] = $field_settings['max'];
        $settings['prefix'] = $field_settings['prefix'];
        $settings['suffix'] = $field_settings['suffix'];
        break;
    }
    return $settings;
  }

}
