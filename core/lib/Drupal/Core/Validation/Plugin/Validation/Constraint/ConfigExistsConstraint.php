<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the value is the name of an existing config object.
 *
 * @Constraint(
 *   id = "ConfigExists",
 *   label = @Translation("Config exists", context = "Validation")
 * )
 */
class ConfigExistsConstraint extends Constraint {

  /**
   * The error message.
   *
   * @var string
   */
  public string $message = "The '@name' config does not exist.";

}
