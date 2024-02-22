<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\Cidr;

/**
 * Cidr constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "Cidr",
 *   label = @Translation("CIDR range", context = "Validation"),
 *   type = false
 * )
 */
class CidrConstraint extends Cidr {

  public $netmaskRangeViolationMessage = 'The value of the netmask should be between %min and %max.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\CidrValidator';
  }

}
