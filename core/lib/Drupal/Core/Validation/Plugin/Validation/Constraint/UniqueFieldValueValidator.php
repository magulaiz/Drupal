<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a field is unique for the given entity type.
 *
 * Supports top-level properties on both content and config entity types.
 */
class UniqueFieldValueValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items_or_value, Constraint $constraint) {
    $field_name = $this->context->getPropertyPath();
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->context->getRoot()->getValue();
    $entity_type_id = $entity->getEntityTypeId();
    $id_key = $entity->getEntityType()->getKey('id');

    $query = \Drupal::entityQuery($entity_type_id)
      ->accessCheck(FALSE);

    $entity_id = $entity->id();
    // Using isset() instead of !empty() as 0 and '0' are valid ID values for
    // entity types using string IDs.
    if (isset($entity_id)) {
      $query->condition($id_key, $entity_id, '<>');
    }

    $value = is_array($items_or_value) ? $items_or_value->first()->value : $items_or_value;
    $value_taken = (bool) $query
      ->condition($field_name, $value)
      ->range(0, 1)
      ->count()
      ->execute();

    if ($value_taken) {
      $this->context->addViolation($constraint->message, [
        '%value' => $value,
        '@entity_type' => $entity->getEntityType()->getSingularLabel(),
        '@field_name' => is_array($items_or_value)
          ? $items_or_value->getFieldDefinition()->getLabel()
          : $field_name,
      ]);
    }
  }

}
