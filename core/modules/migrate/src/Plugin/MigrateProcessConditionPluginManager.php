<?php

namespace Drupal\migrate\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages migrate process condition plugins.
 *
 * @ingroup migration
 */
class MigrateProcessConditionPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/migrate/process/condition', $namespaces, $module_handler, 'Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface', 'Drupal\migrate\Annotation\MigrateProcessConditionPlugin');
  }

}
