<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\LessThan;

/**
 * LessThan constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "LessThan",
 *   label = @Translation("Less than", context = "Validation"),
 *   type = false
 * )
 */
class LessThanConstraint extends LessThan {

  public $message = 'This value should be less than %compared_value.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\LessThanValidator';
  }

}
