<?php

namespace Drupal\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Defines a compiler pass to merge moved classes into a single container parameter.
 */
class BackwardsCompatibilityClassLoaderPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $moved_classes = $container->getParameter('core.moved_classes');
    $modules = array_keys($container->getParameter('container.modules'));
    foreach ($modules as $module) {
      $parameter_name = $module . '.moved_classes';
      if ($container->hasParameter($parameter_name)) {
        $moved_classes = $moved_classes + $container->getParameter($parameter_name);

      }
    }
    $container->setParameter('moved_classes', $moved_classes);
  }

}
