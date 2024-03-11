<?php

declare(strict_types = 1);

namespace Drupal\Core\Config\Plugin\Validation\Constraint;

use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the RequiredConfigDependencies constraint.
 */
class RequiredConfigDependenciesConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected readonly ConfigManagerInterface $configManager
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $entity, Constraint $constraint) {
    assert($constraint instanceof RequiredConfigDependenciesConstraint);

    // Config entities can be represented using either ConfigEntityAdapter or a
    // plain array.
    // @see \Drupal\Core\Entity\Plugin\DataType\ConfigEntityAdapter::createFromEntity()
    // @see \Drupal\Core\Config\TypedConfigManager::processDefinition()
    if (!$entity instanceof ConfigEntityInterface && !is_array($entity)) {
      throw new UnexpectedTypeException($entity, ConfigEntityInterface::class);
    }

    $config_dependencies = $entity instanceof ConfigEntityInterface
      ? ($entity->getDependencies()['config'] ?? [])
      : ($entity['dependencies']['config'] ?? []);

    $validated_entity_type_id = $entity instanceof ConfigEntityInterface
      ? $entity->getEntityTypeId()
      : $this->configManager->getEntityTypeIdByName($this->context->getObject()->getName());

    foreach ($constraint->entityTypes as $entity_type_id) {
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);

      if (!$entity_type instanceof ConfigEntityTypeInterface) {
        throw new LogicException("'$entity_type_id' is not a config entity type.");
      }

      // Ensure the current entity type's config prefix is found in the config
      // dependencies of the entity being validated.
      $pattern = sprintf('/^%s\\.\\w+/', $entity_type->getConfigPrefix());
      if (!preg_grep($pattern, $config_dependencies)) {
        $this->context->addViolation($constraint->message, [
          '@entity_type' => $this->entityTypeManager->getDefinition($validated_entity_type_id)->getSingularLabel(),
          '@dependency_type' => $entity_type->getSingularLabel(),
        ]);
      }
    }
  }

}
