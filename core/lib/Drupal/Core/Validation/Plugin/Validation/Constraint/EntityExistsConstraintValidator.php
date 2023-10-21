<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks the value represents an extant entity.
 *
 * @Constraint(
 *   id = "EntityExists",
 *   label = @Translation("Entity exists", context = "Validation")
 * )
 */
class EntityExistsConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager) {}

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    if (!$constraint instanceof EntityExistsConstraint) {
      throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\EntityExistsConstraint');
    }

    // If we can load an entity of the given type with the ID in value..
    if ($this->entityTypeManager->getStorage($constraint->entityType)->load($value)) {
      // ..we pass the constraint.
      return;
    }

    $violation = $this->context
      ->buildViolation($constraint->message)
      ->setParameter('@entity_type', $constraint->entityType)
      ->setParameter('%value', $value);
    $violation->addViolation();
  }

}
