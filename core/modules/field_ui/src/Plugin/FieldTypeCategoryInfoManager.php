<?php

namespace Drupal\field_ui\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Defines a field type category info plugin manager.
 *
 * A module can define field type categories in a
 * MODULE_NAME.field_type_category_info.yml file contained in the module's
 * base directory. Each plugin has the following structure:
 * @code
 *   CATEGORY_NAME:
 *     label: STRING
 *     description: STRING
 *     weight: INTEGER
 * @endcode
 * For example:
 * @code
 * text:
 *   label: Text
 *   description: Text fields.
 *   weight: 2
 * @endcode
 *
 * @see \Drupal\field_ui\Plugin\FieldTypeCategoryInfoInterface
 * @see \Drupal\field_ui\Plugin\FieldTypeCategoryInfo
 */
class FieldTypeCategoryInfoManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  protected $defaults = [
    'label' => '',
    'description' => '',
    'weight' => NULL,
    'class' => 'Drupal\field_ui\Plugin\FieldTypeCategoryInfo',
  ];

  /**
   * Constructs a new FieldTypeCategoryInfoManager.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   */
  public function __construct(ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend) {
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, 'field_type_category_info_plugins', ['field_type_category_info']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('field_type_category_info', $this->moduleHandler->getModuleDirectories());
    }
    return $this->discovery;
  }

}
