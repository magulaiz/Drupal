<?php

namespace Drupal\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * An interface that should be implemented by classes that depend on a container.
 *
 * @ingroup container
 */
interface ContainerAwareInterface {

  /**
   * Sets the service container.
   */
  public function setContainer(?ContainerInterface $container);

}
