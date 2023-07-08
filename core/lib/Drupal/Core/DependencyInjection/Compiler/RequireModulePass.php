<?php

namespace Drupal\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Removes services that depend on non-installed modules.
 */
final class RequireModulePass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    $module_list = $container->getParameter('container.modules');
    foreach ($container->findTaggedServiceIds('require_module') as $service_id => $tags) {
      foreach ($tags as $tag) {
        $module = $tag['module'];
        if (!isset($module_list[$module])) {
          $container->removeDefinition($service_id);
        }
      }
    }
  }

}
