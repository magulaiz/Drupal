<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if an entity field has changed.
 *
 * It should be used for fields that the value is set when the entity is created
 * and it should never change, even if the initial value is NULL. For example,
 * it could be added as a constraint on the user entity email or account name
 * fields for applications that these are not allowed to change.
 *
 * @Constraint(
 *   id = "ImmutableField",
 *   label = @Translation("Immutable field constraint", context = "Validation"),
 * )
 */
class ImmutableFieldConstraint extends Constraint {

  /**
   * The message to display when validation fails.
   *
   * @var string
   */
  public $message = '@field_name cannot be changed on @entity_type entities.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Drupal\Core\Validation\Plugin\Validation\Constraint\ImmutableFieldValidator';
  }

}
