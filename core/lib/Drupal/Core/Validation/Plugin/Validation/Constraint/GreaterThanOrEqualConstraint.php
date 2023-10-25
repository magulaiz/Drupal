<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

/**
 * GreaterThanOrEqual constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "GreaterThanOrEqual",
 *   label = @Translation("Greater than or equal", context = "Validation"),
 *   type = false
 * )
 */
class GreaterThanOrEqualConstraint extends GreaterThanOrEqual {

  public $message = 'This value should be greater than or equal to %compared_value.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\GreaterThanOrEqualValidator';
  }

}
