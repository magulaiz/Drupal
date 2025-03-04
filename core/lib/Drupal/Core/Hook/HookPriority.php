<?php

declare(strict_types=1);

namespace Drupal\Core\Hook;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Helper class for HookCollectorPass to change the priority of listeners.
 *
 * @internal
 */
class HookPriority {

  public function __construct(protected ContainerBuilder $container) {}

  /**
   * Set the priority of a listener.
   *
   * @param string $class
   *   The name of the class, this is the same as the service id.
   * @param int $key
   *   The key within the tags array of the 'kernel.event_listener' tag for the
   *   hook implementation to be changed.
   * @param int $priority
   *   The new priority.
   */
  public function set(string $class, int $key, int $priority): void {
    $definition = $this->container->findDefinition($class);
    $tags = $definition->getTags();
    $tags['kernel.event_listener'][$key]['priority'] = $priority;
    $definition->setTags($tags);
  }

}
