<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\Regex;

/**
 * Regex constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "Regex",
 *   label = @Translation("Regex", context = "Validation")
 * )
 */
class RegexConstraint extends Regex {

  public string $message = 'This value is not valid.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy(): string {
    return '\Symfony\Component\Validator\Constraints\RegexValidator';
  }

}
