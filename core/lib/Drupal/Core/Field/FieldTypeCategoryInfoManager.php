<?php

namespace Drupal\Core\Field;

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
 * @see \Drupal\Core\Field\FieldTypeCategoryInfoInterface
 * @see \Drupal\Core\Field\FieldTypeCategoryInfo
 */
class FieldTypeCategoryInfoManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  protected $defaults = [
    'label' => '',
    'description' => '',
    'weight' => NULL,
    'class' => FieldTypeCategoryInfo::class,
  ];

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * Constructs a new FieldTypeCategoryInfoManager.
   *
   * @param string $root
   *   The app root.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   */
  public function __construct($root, \Traversable $namespaces, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend) {
    parent::__construct('', $namespaces, $module_handler, FieldTypeCategoryInfoInterface::class, FieldTypeCategoryInfo::class);
    $this->root = $root;
    $this->alterInfo('category_info');
    $this->setCacheBackend($cache_backend, 'field_type_category_info_plugins', ['field_type_category_info']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $directories = ['core' => $this->root . '/core'];
      $directories += $this->moduleHandler->getModuleDirectories();
      $this->discovery = new YamlDiscovery('field_type_category_info', $directories);
      $this->discovery
        ->addTranslatableProperty('label')
        ->addTranslatableProperty('description');
    }
    return $this->discovery;
  }

}
