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

    if ($constraint->callback && $constraint->callbackArgs) {
      list($service, $method) = $this->parseCallback($constraint->callback);
      $arguments = $this->resolveArguments($constraint->callbackArgs);
      $choices = call_user_func_array([$service, $method], $arguments);
      $constraint->choices = $choices;
      $constraint->callback = NULL;
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
    if ($count = substr_count($callback, ':')) {
      if ($count == 1) {
        [$service, $method] = explode(':', $callback, 2);
      }
      if (!\Drupal::hasService($service)) {
        throw new InvalidArgumentException(sprintf('The service "%s" does not exist.', $service));
      }
      $serviceInstance = \Drupal::service($service);
      if (!method_exists($serviceInstance, $method)) {
        throw new InvalidArgumentException(sprintf('The method "%s" does not exist on service "%s".', $method, $service));
      }
      return [$serviceInstance, $method];
    }
    return $callback;
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
      if (is_string($value) && strpos($value, 'array_keys') !== FALSE) {
        $resolvedArguments[$key] = array_keys($value);
      }
      else {
        $resolvedArguments[$key] = $value;
      }
    }
    return $resolvedArguments;
  }

}
