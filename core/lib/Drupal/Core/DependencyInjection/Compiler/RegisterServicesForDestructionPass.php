<?php

namespace Drupal\Core\DependencyInjection\Compiler;

use Drupal\Core\DestructableInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds services to the "kernel.destructable_services" container parameter.
 *
 * Only services tagged with "needs_destruction" are added.
 *
 * @see \Drupal\Core\DestructableInterface
 */
class RegisterServicesForDestructionPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    foreach ($container->getDefinitions() as $definition) {
      if (is_a($definition->getClass(), DestructableInterface::class, TRUE)) {
        $definition->addTag('needs_destruction');
      }
    }
    $services = $container->findTaggedServiceIds('needs_destruction');
    $container->setParameter('kernel.destructable_services', array_keys($services));
  }

}
