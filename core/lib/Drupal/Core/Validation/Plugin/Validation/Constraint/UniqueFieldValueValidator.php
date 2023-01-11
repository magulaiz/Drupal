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
    $matching_entity_ids = $query
      ->condition($field_name, $value)
      ->execute();

    if (empty($matching_entity_ids)) {
      return;
    }

    if ($entity->isNew()) {
      $this->context->addViolation($constraint->message, [
        '%value' => $value,
        '@entity_type' => $entity->getEntityType()->getSingularLabel(),
        '@field_name' => is_array($items_or_value)
          ? $items_or_value->getFieldDefinition()->getLabel()
          : $field_name,
      ]);
    }
    // If not new, the only match must be *this* entity.
    else if ($entity_id != reset($matching_entity_ids)) {
      // @todo decide how to handle this without breaking BC — I think logging? For now, just an exception to allow making it relaible.
      throw new \Exception(sprintf(
        'The existing %s entity (ID: %s) is being validated and it violates a uniqueness constraint: %s have the same value for the "%s" field: "%s".',
        $entity_type_id,
        $entity_id,
        implode($matching_entity_ids),
        $field_name,
        (string) $value
      ));
    }
  }

}
