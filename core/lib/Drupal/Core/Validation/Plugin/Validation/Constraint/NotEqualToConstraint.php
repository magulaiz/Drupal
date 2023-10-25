<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\NotEqualTo;

/**
 * NotEqualTo constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "NotEqualTo",
 *   label = @Translation("Not equal to", context = "Validation"),
 *   type = false
 * )
 */
class NotEqualToConstraint extends NotEqualTo {

  public $message = 'This value should not be equal to %compared_value.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\NotEqualToValidator';
  }

}
