<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that a bundle exists on a certain content entity type.
 */
class EntityBundleExistsConstraintValidator extends ConstraintValidator {

  use TreeAwareConstraintTrait;

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    assert($constraint instanceof EntityBundleExistsConstraint);

    if (!is_string($value)) {
      throw new UnexpectedTypeException($value, 'string');
    }

    // @see \Drupal\Core\Config\TypedConfigManager::buildDataDefinition()
    // @todo generalize: support multiple `%parent` occurrences, support hardcoded value
    assert(str_starts_with($constraint->entityTypeId, '%parent.'));
    $mapping = $this->getParentProperty();
    $entity_type_id_property_path = str_replace('%parent.', '', $constraint->entityTypeId);
    $entity_type_id = $mapping->get($entity_type_id_property_path)->getValue();

    $entity_type_bundle_info = \Drupal::service('entity_type.bundle.info');
    assert($entity_type_bundle_info instanceof EntityTypeBundleInfoInterface);
    $bundles = $entity_type_bundle_info->getBundleInfo($entity_type_id);

    if (!array_key_exists($value, $bundles)) {
      $this->context->addViolation($constraint->message, [
        '@bundle' => $value,
        '@entity_type_id' => $entity_type_id,
      ]);
    }
  }

}
