<?php

namespace Drupal\phpass;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Remove password.core_backward_compat service if phpass module is enabled.
 */
class PhpassServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('password.core_backward_compat')) {
      $container->removeDefinition('password.core_backward_compat');
    }
  }

}
