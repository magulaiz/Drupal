<?php

declare(strict_types=1);

namespace Drupal\field\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\TypeResolver;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;
use Symfony\Component\Validator\ConstraintValidator;

class NoEntitiesExistYetWithHigherCardinalityValidator extends ConstraintValidator implements ContainerInjectionInterface {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $cardinality, SymfonyConstraint $constraint): void {
    assert($constraint instanceof NoEntitiesExistYetWithHigherCardinality);

    if ($cardinality === FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      return;
    }

    /**
     * We cannot check this constraint if the field storage does not exist.
     */
    $fieldStorageConfig = $this->entityTypeManager->getStorage('field_storage_config')
      ->load($constraint->entityType . '.' . $constraint->fieldName);
    if ($fieldStorageConfig === NULL) {
      return;
    }

    $object = $this->context->getObject();
    assert($object instanceof TypedDataInterface);

    $entity_type = TypeResolver::resolveExpression($constraint->entityType, $object);
    $field_name = TypeResolver::resolveExpression($constraint->fieldName, $object);

    $max_delta_alias = 'max_delta';
    $result = $this->entityTypeManager->getStorage($entity_type)
      ->getAggregateQuery()
      ->accessCheck(FALSE)
      ->aggregate($field_name . '.%delta', 'MAX', NULL, $max_delta_alias)
      ->execute();

    $max_delta = 0;
    if (is_array($result) && !empty($result)) {
      $max_delta = $result[0][$max_delta_alias] ?? 0;
    }

    if ($max_delta > $cardinality) {
      $this->context->addViolation($constraint->message, [
        '@entity_type' => $entity_type,
        '@field_name' => $field_name,
        '@max_delta' => $max_delta,
        '@cardinality' => $cardinality,
      ]);
    }
  }

}
