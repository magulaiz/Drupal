<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Hook;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\HookPriority;
use Drupal\Core\Hook\Order;
use Drupal\Tests\UnitTestCase;

/**
 * Base class for testing HookPriority.
 */
abstract class HookPriorityTestBase extends UnitTestCase {

  /**
   * The container builder.
   *
   * @var \Drupal\Core\DependencyInjection\ContainerBuilder
   */
  protected ContainerBuilder $container;

  /**
   * Set up three service listeners, "a", "b" and "c".
   *
   * The service id, the class name and the method name are all the same.
   *
   * @param bool $different_priority
   *   When TRUE, "c" will fire first, "b" second and "a" last. When FALSE,
   *   the priority will be set to be the same and the order is undefined.
   */
  protected function setUpContainer(bool $different_priority): void {
    $this->container = new ContainerBuilder();
    foreach (['a', 'b', 'c'] as $key => $name) {
      $definition = $this->container
        ->register($name, $name)
        ->setAutowired(TRUE);
      $definition->addTag('kernel.event_listener', [
        'event' => 'drupal_hook.test',
        'method' => $name,
        // Do not use $key itself to avoid a 0 priority which could potentially
        // lead to misleading results.
        'priority' => $different_priority ? $key + 3 : 0,
      ]);
    }
  }

  /**
   * Get the priority for a service.
   */
  protected function getPriority(string $name): int {
    $definition = $this->container->getDefinition($name);
    return $definition->getTags()['kernel.event_listener'][0]['priority'];
  }

  /**
   * Change priority of a class and method.
   *
   * @param class-string $classBeingChanged
   *   The class being changed, the method has the same name.
   * @param \Drupal\Core\Hook\Order|class-string $order
   *   Either a member of the Order enum or the name of a ComplexOrder class.
   * @param class-string $relativeTo
   *   If the operation is before or after, this is the name of the class
   *   the operation changes relative to.
   */
  protected function doPriorityChange(string $classBeingChanged, Order|string $order, string $relativeTo = ''): void {
    if ($relativeTo) {
      // The modules / classesAndMethods argument of the order class is
      // processed in HookCollectorPass and is ignored by HookPriority, they
      // are passed to HookPriority in the $other_specifiers argument.
      $hook = new Hook('test', order: new $order(modules: ['']));
      $other_specifiers = ["$relativeTo::$relativeTo"];
    }
    else {
      $hook = new Hook('test', order: $order);
      $other_specifiers = NULL;
    }
    $hook->set(class: $classBeingChanged, module: '', method: $classBeingChanged);
    (new HookPriority($this->container))->change('drupal_hook.test', $hook, $other_specifiers);
  }

}
