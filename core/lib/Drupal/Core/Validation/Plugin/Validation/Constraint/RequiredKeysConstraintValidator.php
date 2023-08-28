<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the RequiredKeys constraint.
 */
class RequiredKeysConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    assert($constraint instanceof RequiredKeysConstraint);

    if (!is_array($value)) {
      throw new UnexpectedTypeException($value, 'array');
    }

    $required_keys = $constraint->getRequiredKeys($this->context);

    $missing_keys = array_diff($required_keys['unconditional'], array_keys($value));
    foreach ($missing_keys as $key) {
      $this->context->addViolation($constraint->message, ['@key' => $key]);
    }

    $missing_conditional_keys = array_diff(array_keys($required_keys['conditional']), array_keys($value));
    foreach ($missing_conditional_keys as $key) {
      $this->context->addViolation($constraint->conditionalMessage, ['@key' => $key] + $required_keys['conditional'][$key]);
    }

    $extraneous_keys = array_intersect(array_keys($required_keys['extraneous']), array_keys($value));
    foreach ($extraneous_keys as $key) {
      $this->context->addViolation($constraint->extraneousMessage, ['@key' => $key] + $required_keys['extraneous'][$key]);
    }
  }

}
