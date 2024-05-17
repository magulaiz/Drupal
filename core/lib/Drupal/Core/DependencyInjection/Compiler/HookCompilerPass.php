<?php

namespace Drupal\Core\DependencyInjection\Compiler;

use Drupal\Core\Extension\HookHelper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tags hook classes as event listeners.
 */
class HookCompilerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $hook_implementations = $container->getParameter(HookHelper::HOOK_IMPLEMENTATIONS);
    foreach ($hook_implementations as $hook => $class_implementations) {
      foreach ($class_implementations as $class => $class_implementation) {
        foreach ($class_implementation as $method => $implementation) {
          $container->getDefinition($class)->addTag('kernel.event_listener', [
            'event' => "drupal_hook.$hook",
            'method' => $method,
            'priority' => $implementation['priority'],
          ]);
        }
      }
    }
  }

}
