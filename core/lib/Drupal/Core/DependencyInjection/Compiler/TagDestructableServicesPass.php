<?php

namespace Drupal\Core\DependencyInjection\Compiler;

use Drupal\Core\DestructableInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Automatically tags services with needs_destruction.
 *
 * @see \Drupal\Core\DestructableInterface
 */
class TagDestructableServicesPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $container->registerForAutoconfiguration(DestructableInterface::class)
      ->addTag('needs_destruction');
  }

}
