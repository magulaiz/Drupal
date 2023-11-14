<?php

namespace Drupal\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a standard way to access the service container.
 */
trait ContainerAwareTrait {

  /**
   * The service container.
   */
  protected ?ContainerInterface $container;

  /**
   * Sets the service container.
   */
  public function setContainer(?ContainerInterface $container) {
    $this->container = $container;
  }

}
