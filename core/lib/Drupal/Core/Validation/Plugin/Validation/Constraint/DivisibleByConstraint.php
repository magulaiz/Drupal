<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\DivisibleBy;

/**
 * DivisibleBy constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "DivisibleBy",
 *   label = @Translation("Divisible by", context = "Validation"),
 *   type = false
 * )
 */
class DivisibleByConstraint extends DivisibleBy {

  public $message = 'This value should be a multiple of %compared_value.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\DivisibleByValidator';
  }

}
