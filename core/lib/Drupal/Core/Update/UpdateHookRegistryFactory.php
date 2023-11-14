<?php

namespace Drupal\Core\Update;

use Drupal\Core\DependencyInjection\ContainerAwareInterface;
use Drupal\Core\DependencyInjection\ContainerAwareTrait;

/**
 * Service factory for the versioning update registry.
 */
class UpdateHookRegistryFactory implements ContainerAwareInterface {

  use ContainerAwareTrait;

  /**
   * Creates a new UpdateHookRegistry instance.
   *
   * @return \Drupal\Core\Update\UpdateHookRegistry
   *   The update registry instance.
   */
  public function create() {
    return new UpdateHookRegistry(
      array_keys($this->container->get('module_handler')->getModuleList()),
      $this->container->get('keyvalue')->get('system.schema')
    );
  }

}
