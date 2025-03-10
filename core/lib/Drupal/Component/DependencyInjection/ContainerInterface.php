<?php

namespace Drupal\Component\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface as BaseContainerInterface;

/**
 * The interface for Drupal service container classes.
 */
interface ContainerInterface extends BaseContainerInterface {

  /**
   * Gets all defined service IDs.
   *
   * @return array
   *   An array of all defined service IDs.
   */
  public function getServiceIds();

  /**
   * Set a plugin parameter (even on frozen parameter bags).
   *
   * @param string $plugin_type
   *   The plugin type.
   * @param string $name
   *   The name.
   * @param mixed $value
   *   The value.
   */
  public function setPluginParameter(string $plugin_type, string $name, mixed $value): void;

  /**
   * Get a plugin parameter (without throwing an exception).
   *
   * @param string $plugin_type
   *   The plugin type.
   * @param string $name
   *   The name.
   *
   * @return mixed
   *   The value of the parameter or NULL if it doesn't exist.
   */
  public function getPluginParameter(string $plugin_type, string $name): mixed;

}
