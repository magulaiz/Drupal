<?php

namespace Drupal\migrate_drupal;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Alters container services.
 */
class MigrateDrupalServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    parent::alter($container);

    $container->getDefinition('plugin.manager.migration')
      ->setClass(MigrationPluginManager::class);
  }

}
