<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\GreaterThan;

/**
 * GreaterThan constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "GreaterThan",
 *   label = @Translation("Greater than", context = "Validation"),
 *   type = false
 * )
 */
class GreaterThanConstraint extends GreaterThan {

  public $message = 'This value should be a multiple of %compared_value.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\GreaterThanValidator';
  }

}
