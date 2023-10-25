<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\Type;

/**
 * Type constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "Type",
 *   label = @Translation("Type", context = "Validation"),
 *   type = false
 * )
 */
class TypeConstraint extends Type {

  public $message = 'This value should be of type %type.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\TypeValidator';
  }

}
