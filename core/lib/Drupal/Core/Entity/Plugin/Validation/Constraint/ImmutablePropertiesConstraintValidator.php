<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity\Plugin\Validation\Constraint;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\StorageInterface;
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

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected readonly StorageInterface $configStorage,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.storage'),
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

      $id = $value->getOriginalId() ?: $value->id();
      if (empty($id)) {
        throw new LogicException('The entity does not have an ID.');
      }
      $original = $this->entityTypeManager->getStorage($value->getEntityTypeId())
        ->loadUnchanged($id);
    }
    else {
      $config_name = $this->context->getObject()->getName();

      // This validation is irrelevant on new entities.
      if (!$this->configStorage->exists($config_name)) {
        return;
      }
      $original = $this->configStorage->read($config_name);
    }

    if (empty($original)) {
      throw new RuntimeException('The original entity could not be loaded.');
    }

    $values = is_array($value) ? $value : $value->toArray();
    $original = is_array($original) ? $original : $original->toArray();
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
