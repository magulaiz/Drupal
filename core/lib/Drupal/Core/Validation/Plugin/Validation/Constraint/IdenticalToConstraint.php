<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\IdenticalTo;

/**
 * IdenticalTo constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "IdenticalTo",
 *   label = @Translation("Identical to", context = "Validation"),
 *   type = false
 * )
 */
class IdenticalToConstraint extends IdenticalTo {

  public $message = 'This value should be identical to %compared_value_type %compared_value.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\IdenticalToValidator';
  }

}
