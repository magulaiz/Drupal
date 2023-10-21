<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks the value represents an extant entity.
 *
 * @Constraint(
 *   id = "EntityExists",
 *   label = @Translation("Entity exists", context = "Validation")
 * )
 */
class EntityExistsConstraint extends Constraint {

  public $entityType;

  public $message = 'No @entity_type with ID %value exists.';

  /**
   * {@inheritdoc}
   */
  public function __construct(mixed $options = NULL, array $groups = NULL, mixed $payload = NULL) {
    parent::__construct($options, $groups, $payload);
  }

}
