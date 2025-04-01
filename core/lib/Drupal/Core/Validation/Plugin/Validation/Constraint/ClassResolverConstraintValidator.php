<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that a given extension exists.
 */
class ClassResolverConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  public function __construct(protected ContainerInterface $container) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {

    if (!$constraint instanceof ClassResolverConstraint) {
      throw new UnexpectedTypeException($constraint, ClassResolverConstraint::class);
    }
    $service = $this->container->get($constraint->callback[0]);
    if ($service === NULL) {
      throw new \InvalidArgumentException('The service "' . $constraint->callback[0] . '" does not exist.');
    }

    if (!method_exists($service, $constraint->callback[1])) {
      throw new \InvalidArgumentException('The method "' . $constraint->callback[1] . '" does not exist on the service "' . $constraint->callback[0] . '".');
    }

    $result = $service->{$constraint->callback[1]}($value);
    if ($result !== TRUE) {
      $this->context->buildViolation($constraint->message)
        ->setParameter('@service', $constraint->callback[0])
        ->setParameter('@method', $constraint->callback[1])
        ->addViolation();
    }
  }

}
