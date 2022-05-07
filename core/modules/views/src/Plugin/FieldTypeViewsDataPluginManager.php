<?php

namespace Drupal\views\Plugin;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\views\Annotation\ViewsFieldData;
use Drupal\views\Plugin\views\FieldTypeViewsDataInterface;

/**
 * Plugin manager for field views data plugins.
 *
 * @ingroup views_plugins
 */
class FieldTypeViewsDataPluginManager extends DefaultPluginManager {

  /**
   * Lookup array of plugin IDs for field type IDs.
   *
   * @var array
   */
  protected $getPluginIdForFieldType = [];

  /**
   * Constructs a FieldTypeViewsDataPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/views/field_views_data', $namespaces, $module_handler, FieldTypeViewsDataInterface::class, ViewsFieldData::class);

    $this->alterInfo('views_field_type_views_data');

    $this->defaults = [
      'argument' => [
        'id' => 'standard',
      ],
      'field' => [
        'id' => 'field',
      ],
      'filter' => [
        'id' => 'standard',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];
  }

  /**
   * Determines whether a plugin exists for a field type.
   *
   * @param string $field_type
   *   The field type ID.
   *
   * @return bool
   *   TRUE if a plugin exists, FALSE if not.
   */
  public function hasPluginForFieldType(string $field_type): bool {
    return (bool) $this->getPluginIdForFieldType($field_type);
  }

  /**
   * Creates an instance of a plugin for a field type.
   *
   * @param string $field_type
   *   The field type ID to instantiate a plugin for
   * @param array $configuration
   *   An array of configuration relevant to the plugin instance.
   *
   * @return object
   *   A fully configured plugin instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the instance cannot be created, such as if there is no plugin for the
   *   given field type.
   *
   * @return
   */
  public function createInstanceForFieldType(string $field_type): FieldTypeViewsDataInterface {
    if ($plugin_id = $this->getPluginIdForFieldType($field_type)) {
      return $this->createInstance($plugin_id);
    }
    else {
      throw new PluginException(sprintf("No plugin found for field type '%s'.", $field_type));
    }
  }

  /**
   * Gets the plugin ID for a given field type.
   *
   * @param string $field_type
   *   The field type ID.
   *
   * @return string|null
   *   The plugin ID, or NULL if none exists.
   */
  protected function getPluginIdForFieldType(string $field_type): ?string {
    if (empty($this->pluginIdsByFieldType)) {
      foreach ($this->getDefinitions() as $plugin_id => $definition) {
        foreach ($definition['field_types'] as $definition_field_type) {
          if (isset($this->pluginIdsByFieldType[$definition_field_type])) {
            throw new InvalidPluginDefinitionException($plugin_id,
              sprintf("The '%s' and '%s' plugins may not both declare they handle the '%s' field type.",
                $this->pluginIdsByFieldType[$definition_field_type],
                $plugin_id,
                $field_type
              ));
          }

          $this->pluginIdsByFieldType[$definition_field_type] = $plugin_id;
        }
      }
    }

    return $this->pluginIdsByFieldType[$field_type] ?? NULL;
  }

}
