<?php

declare(strict_types=1);

namespace Drupal\KernelTests;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\HookCollectorPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Adds hooks from kernel test to event dispatcher and module handler.
 */
class KernelTestCompilerPass implements CompilerPassInterface {

  /**
   * Constructs a KernelTestCompilerPass object.
   *
   * @param \Symfony\Component\DependencyInjection\Definition $definition
   *   The kernel test service definition that will be used to register hooks.
   */
  public function __construct(private Definition $definition) {}

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $map = $container->getParameter('hook_implementations_map');
    // Check for #[Hook] on methods.
    $reflection_class = new \ReflectionClass($this->definition->getClass());
    $priority = -1000;
    foreach ($reflection_class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method_reflection) {
      foreach ($method_reflection->getAttributes(Hook::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute_reflection) {
        $hook = $attribute_reflection->newInstance();
        assert($hook instanceof Hook);
        HookCollectorPass::checkForProceduralOnlyHooks($hook, static::class);
        $hook->setMethod($method_reflection->getName());
        $this->definition->addTag('kernel.event_listener', [
          'event' => "drupal_hook.{$hook->hook}",
          'method' => $hook->method,
          'priority' => $priority--,
        ]);
        $map[$hook->hook][$this->definition->getClass()][$hook->method] = 'core';
      }
    }
    $container->setParameter('hook_implementations_map', $map);
  }

}
