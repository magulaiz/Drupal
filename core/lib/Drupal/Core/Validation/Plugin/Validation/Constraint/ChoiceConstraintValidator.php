<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Utility\CallableResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ChoiceValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates complex data.
 */
class ChoiceConstraintValidator extends ChoiceValidator implements ContainerInjectionInterface {

  /**
   * Constructs a CallableResolver instance.
   *
   * @param \Drupal\Core\Utility\CallableResolver $callableResolver
   *   The callable resolver.
   */
  public function __construct(protected CallableResolver $callableResolver) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('callable_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {

    if (!$constraint instanceof ChoiceConstraint) {
      throw new UnexpectedTypeException($constraint, ChoiceConstraint::class);
    }

    // By default, callback names follow the class::method notation. This class
    // adds the possibility to use a service from the container as a controller
    // by using a service:method notation.
    if ($constraint->callback && $count = substr_count($constraint->callback, ':')) {
      if ($count == 1) {
        if ($callback = $this->callableResolver->getCallableFromDefinition($constraint->callback)) {
          // Process any callback arguments.
          $args = [];
          if (isset($constraint->callbackArgs) && is_array($constraint->callbackArgs)) {
            $args = $this->resolveArguments($constraint->callbackArgs);
          }
          $choices = call_user_func_array($callback, $args);
          // Allow callback results to be transformed.
          if (isset($constraint->transform)) {
            $choices = call_user_func($constraint->transform, $choices);
          }
          if (is_array($choices)) {
            $constraint->choices = $choices;
            // The callback is no longer needed..
            $constraint->callback = NULL;
          }
        }
      }
    }
    parent::validate($value, $constraint);
  }

  /**
   * Parse the arguments.
   *
   * @param array $arguments
   *   The optional arguments.
   *
   * @return array
   *   The array of arguments.
   */
  private function resolveArguments($arguments): array {
    $resolvedArguments = [];
    foreach ($arguments as $key => $value) {
      $resolvedArguments[$key] = $value;
    }
    return $resolvedArguments;
  }

}
