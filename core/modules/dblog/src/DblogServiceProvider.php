<?php

namespace Drupal\dblog;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class DblogServiceProvider extends ServiceProviderBase {
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('logger.dblog')) {
      $definition = $container->getDefinition('logger.dblog');
      $definition->setClass('Drupal\dblog\Logger\Disabled');
    }
  }
}
