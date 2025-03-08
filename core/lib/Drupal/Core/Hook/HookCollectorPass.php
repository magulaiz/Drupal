<?php

declare(strict_types=1);

namespace Drupal\Core\Hook;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Collects and registers hook implementations.
 *
 * A hook implementation is a class in a Drupal\modulename\Hook namespace
 * where either the class itself or the methods have a #[Hook] attribute.
 * These classes are automatically registered as autowired services.
 *
 * Services for procedural implementation of hooks are also registered
 * using the ProceduralCall class.
 *
 * Finally, a hook_implementations_map container parameter is added. This
 * contains a mapping from [hook,class,method] to the module name.
 */
class HookCollectorPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $module_list = $container->getParameter('container.modules');
    $parameters = $container->getParameterBag()->all();
    $skip_procedural_modules = array_filter(
      array_keys($module_list),
      fn (string $module) => !empty($parameters["$module.hooks_converted"]),
    );
    $collector = HookCollector::collectAllHookImplementations($module_list, $skip_procedural_modules);

    $collector->writeToContainer($container);
  }

}
