<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a field has not changed compared to the original entity.
 */
class ImmutableFieldValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $entity = $items->getEntity();
    if ($entity->isNew()) {
      return;
    }

    $original_entity = \Drupal::entityTypeManager()
      ->getStorage($entity->getEntityTypeId())
      ->loadUnchanged($entity->id());

    $field_name = $items->getFieldDefinition()->getName();
    if ($items->equals($original_entity->get($field_name))) {
      return;
    }

    $this->context->addViolation(
      $constraint->message,
      [
        '@entity_type' => $entity->getEntityType()->getSingularLabel(),
        '@field_name' => mb_strtolower($items->getFieldDefinition()->getLabel()),
      ]
    );
  }

}
