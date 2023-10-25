<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\NotIdenticalTo;

/**
 * NotIdenticalTo constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "NotIdenticalTo",
 *   label = @Translation("Not identical to", context = "Validation"),
 *   type = false
 * )
 */
class NotIdenticalToConstraint extends NotIdenticalTo {

  public $message = 'This value should not be identical to %compared_value_type %compared_value.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\NotIdenticalToValidator';
  }

}
