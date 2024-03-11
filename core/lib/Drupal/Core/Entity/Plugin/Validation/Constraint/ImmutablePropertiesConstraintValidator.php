<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity\Plugin\Validation\Constraint;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\RuntimeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Validates the ImmutableProperties constraint.
 */
class ImmutablePropertiesConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Constructs an ImmutablePropertiesConstraintValidator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    assert($constraint instanceof ImmutablePropertiesConstraint);

    if (!$value instanceof ConfigEntityInterface && !is_array($value)) {
      throw new UnexpectedValueException($value, ConfigEntityInterface::class);
    }

    // Config entities can be represented using either ConfigEntityAdapter or a
    // plain array.
    // @see \Drupal\Core\Entity\Plugin\DataType\ConfigEntityAdapter::createFromEntity()
    // @see \Drupal\Core\Config\TypedConfigManager::processDefinition()
    if ($value instanceof ConfigEntityInterface) {
      // This validation is irrelevant on new entities.
      if ($value->isNew()) {
        return;
      }
      $entity_type_id = $value->getEntityTypeId();
      $id = $value->getOriginalId() ?: $value->id();
      if (empty($id)) {
        throw new LogicException('The entity does not have an ID.');
      }
    }
    else {
      // This validation is irrelevant on new entities.
      if (!array_key_exists('uuid', $value)) {
        return;
      }
      // Use the config name to determine the entity type ID and entity ID.
      $config_name = $this->context->getObject()->getName();
      $matching_entity_type = FALSE;
      $entity_type_definitions = $this->entityTypeManager->getDefinitions();
      foreach ($entity_type_definitions as $entity_type) {
        if ($entity_type instanceof ConfigEntityTypeInterface && str_starts_with($config_name, $entity_type->getConfigPrefix() . '.')) {
          $matching_entity_type = $entity_type;
          break;
        }
      }
      if ($matching_entity_type === FALSE) {
        throw new \LogicException(sprintf('Config entity type for %s not found.', $config_name));
      }
      $entity_type_id = $matching_entity_type->id();
      $id = str_replace($matching_entity_type->getConfigPrefix() . '.', '', $config_name);
    }

    $original = $this->entityTypeManager->getStorage($entity_type_id)
      ->loadUnchanged($id);
    if (empty($original)) {
      throw new RuntimeException('The original entity could not be loaded.');
    }

    $values = is_array($value) ? $value : $value->toArray();
    $original = $original->toArray();
    foreach ($constraint->properties as $name) {
      // The property must be concretely defined in the class.
      if (!array_key_exists($name, $values)) {
        throw new LogicException("The entity does not have a '$name' property.");
      }

      if ($original[$name] !== $values[$name]) {
        $this->context->addViolation($constraint->message, ['@name' => $name]);
      }
    }
  }

}
