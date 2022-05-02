<?php

namespace Drupal\views\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin type manager for all views field type views data plugins.
 *
 * @ingroup views_plugins
 */
class FieldTypeViewsDataPluginManager extends DefaultPluginManager {

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
    parent::__construct('Plugin/views/field_views_data', $namespaces, $module_handler, 'Drupal\views\Plugin\views\FieldTypeViewsDataInterface', 'Drupal\views\Annotation\ViewsFieldData');

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

}
