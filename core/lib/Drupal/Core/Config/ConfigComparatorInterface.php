<?php

namespace Drupal\Core\Config;

/**
 * Provides an interface for configuration comparator.
 */
interface ConfigComparatorInterface {

  /**
   * Checks if a config item has been modified since its installation.
   *
   * @param string $config_name
   *   The configuration item's full name.
   *
   * @return bool
   *   Returns TRUE is modified, FALSE if original configuration.
   *
   * @throws ConfigNameException
   *   When configuration is not found.
   */
  public function isModified($config_name);

}
