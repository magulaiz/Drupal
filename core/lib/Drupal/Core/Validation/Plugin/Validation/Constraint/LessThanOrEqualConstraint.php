<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\LessThanOrEqual;

/**
 * LessThanOrEqual constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "LessThanOrEqual",
 *   label = @Translation("Less than or equal", context = "Validation"),
 *   type = false
 * )
 */
class LessThanOrEqualConstraint extends LessThanOrEqual {

  public $message = 'This value should be less than or equal to %compared_value.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\LessThanOrEqualValidator';
  }

}
