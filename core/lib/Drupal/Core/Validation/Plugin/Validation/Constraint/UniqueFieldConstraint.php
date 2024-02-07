<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if an entity field has a unique value.
 *
 * @Constraint(
 *   id = "UniqueField",
 *   label = @Translation("Unique field constraint", context = "Validation"),
 * )
 */
class UniqueFieldConstraint extends Constraint {

  public $message = 'A @entity_type with @field_name %value already exists.';

  /**
   * Returns the name of the class that validates this constraint.
   *
   * @return string
   */
  public function validatedBy() {
    return '\Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldValueValidator';
  }

  /**
   * Checks if uniqueness should ignore case.
   *
   * To determine uniqueness without considering case, override this method and
   * return TRUE.
   *
   * @return bool
   *   TRUE if case should not be considered when determining uniqueness.
   */
  public function shouldIgnoreCase(): bool {
    return FALSE;
  }

}
