<?php

namespace Drupal\field\Plugin\migrate\process\d6;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Get the field settings.
 *
 * @MigrateProcessPlugin(
 *   id = "field_settings"
 * )
 */
class FieldSettings extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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
    // To maintain backwards compatibility, ensure that $value contains at least
    // three elements.
    if (count($value) == 2) {
      $value[] = NULL;
    }
    [$field_type, $global_settings, $original_field_type] = $value;
    if ($original_field_type == 'text') {
      $original_field_type = 'd6_' . $original_field_type;
    }
    try {
      return $this->fieldPluginManager->createInstance($original_field_type, ['core' => 6])
        ->transformFieldStorageSettings($row);
    }
    catch (PluginNotFoundException $e) {
      return $this->getSettings($field_type, $global_settings, $original_field_type);
    }
  }

  /**
   * Merge the default D8 and specified D6 settings.
   *
   * @param string $field_type
   *   The destination field type.
   * @param array $global_settings
   *   The field settings.
   * @param string $original_field_type
   *   (optional) The original field type before migration.
   *
   * @return array
   *   A valid array of settings.
   */
  public function getSettings($field_type, $global_settings, $original_field_type = NULL) {
    $max_length = $global_settings['max_length'] ?? '';
    $max_length = empty($max_length) ? 255 : $max_length;
    $allowed_values = [];
    if (isset($global_settings['allowed_values'])) {
      $list = explode("\n", $global_settings['allowed_values']);
      $list = array_map('trim', $list);
      $list = array_filter($list, 'strlen');
      switch ($field_type) {
        case 'list_string':
        case 'list_integer':
        case 'list_float':
          foreach ($list as $value) {
            $value = explode("|", $value);
            $allowed_values[$value[0]] = $value[1] ?? $value[0];
          }
          break;

        default:
          $allowed_values = $list;
      }
    }

    $settings = [
      'list_string' => [
        'allowed_values' => $allowed_values,
      ],
      'list_integer' => [
        'allowed_values' => $allowed_values,
      ],
      'list_float' => [
        'allowed_values' => $allowed_values,
      ],
      'boolean' => [
        'allowed_values' => $allowed_values,
      ],
    ];

    return isset($settings[$field_type]) ? $settings[$field_type] : [];
  }

}
