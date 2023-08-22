<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the ConditionallyRequiredKeys constraint.
 */
class ConditionallyRequiredKeysConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    assert($constraint instanceof ConditionallyRequiredKeysConstraint);

    if (!is_array($value)) {
      throw new UnexpectedTypeException($value, 'array');
    }

    $constraint->validateOptions($this->context);

    // It should be safe to access this array index because $condition_key is
    // guaranteed to be a required key. But unfortunately, there's no way to
    // ensure constraints are validated in a specific order, so we still need to
    // be careful.
    // @see ConditionallyRequiredKeysConstraint::validateOptions()
    if (!array_key_exists($constraint->condition['key'], $value)) {
      return;
    }

    if ($value[$constraint->condition['key']] === $constraint->condition['value']) {
      $missing_keys = array_diff($constraint->keys, array_keys($value));
      foreach ($missing_keys as $key) {
        $this->context->addViolation($constraint->message, ['@key' => $key]);
      }
    }
    else {
      $extraneous_keys = array_intersect($constraint->keys, array_keys($value));
      foreach ($extraneous_keys as $key) {
        $this->context->addViolation($constraint->extraneousMessage, ['@key' => $key]);
      }
    }
  }

}
