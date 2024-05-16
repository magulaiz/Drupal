<?php

namespace Drupal\Core\Extension\Dependency;

/**
 * Defines an interface for a project dependency.
 */
interface DependencyInterface {

  /**
   * Determines if the provided version is compatible with this dependency.
   *
   * @param string $version
   *   The version to check, for example '4.2'.
   *
   * @return bool
   *   TRUE if compatible with the provided version, FALSE if not.
   */
  public function isCompatible($version);

  /**
   * Gets the dependency name.
   *
   * @return string
   *   Dependency name.
   */
  public function getName();

  /**
   * Gets the constraint string from the dependency.
   *
   * @return string
   *   The constraint string.
   */
  public function getConstraintString();

}
