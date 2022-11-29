<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a config entity has valid dependencies.
 *
 * @Constraint(
 *   id = "ConfigDependencies",
 *   label = @Translation("Config dependencies", context = "Validation"),
 *   type = { "entity" }
 * )
 */
class ConfigDependenciesConstraint extends Constraint {

  /**
   * The error message if a config dependency does not exist.
   *
   * @var string
   */
  public string $unknownConfigMessage = "The '@name' config does not exist.";

  /**
   * The error message if a module dependency is not installed.
   *
   * @var string
   */
  public string $unknownModuleMessage = "Module '@name' is not installed.";

  /**
   * The error message if a theme dependency is not installed.
   *
   * @var string
   */
  public string $unknownThemeMessage = "Theme '@name' is not installed.";

}
