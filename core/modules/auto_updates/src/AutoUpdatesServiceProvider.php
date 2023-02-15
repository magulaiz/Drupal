<?php

declare(strict_types = 1);

namespace Drupal\auto_updates;

use Drupal\auto_updates\Validator\XdebugValidator;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies container services for Automatic Updates.
 */
class AutoUpdatesServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $service_id = 'package_manager.validator.xdebug';
    if ($container->hasDefinition($service_id)) {
      $container->getDefinition($service_id)
        ->setClass(XdebugValidator::class);
    }
  }

}
