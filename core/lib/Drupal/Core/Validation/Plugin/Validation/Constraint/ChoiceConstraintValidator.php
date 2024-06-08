<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use InvalidArgumentException;
use Drupal\Core\TypedData\Validation\TypedDataAwareValidatorTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ChoiceValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates complex data.
 */
class ChoiceConstraintValidator extends ChoiceValidator {

  use TypedDataAwareValidatorTrait;

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
        list($service, $method) = $this->parseCallback($constraint->callback);
        $args = [];
        if (isset($constraint->callbackArgs)) {
          $args = $this->resolveArguments($constraint->callbackArgs);
        }
        $choices = call_user_func_array([$service, $method], $args);
        if (isset($constraint->transform)) {
          $choices = call_user_func($constraint->transform, $choices);
        }
        $constraint->choices = $choices;
        $constraint->callback = NULL;
      }
    }
    parent::validate($value, $constraint);
  }

  /**
   * Handle service callbacks in the form of service:method.
   *
   * @param string $callback
   *   The name of the service and method passed in as a callback.
   *
   * @return callable
   *   The callback.
   */
  private function parseCallback($callback): array {
    [$service, $method] = explode(':', $callback, 2);
    if (!\Drupal::hasService($service)) {
      throw new InvalidArgumentException(sprintf('The service "%s" does not exist.', $service));
    }
    $serviceInstance = \Drupal::service($service);
    if (!method_exists($serviceInstance, $method)) {
      throw new InvalidArgumentException(sprintf('The method "%s" does not exist on service "%s".', $method, $service));
    }
    return [$serviceInstance, $method];
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
      if (is_string($value)) {
        $resolvedArguments[$key] = $value;
      }
      else {
        $resolvedArguments[$key] = $value;
      }
    }
    return $resolvedArguments;
  }

}
