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
   * The name of the property to check.
   *
   * @var string
   */
  public $propertyName = NULL;

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldValueValidator';
  }

}
