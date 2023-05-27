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

    $missing_keys = array_diff(
      $constraint->getRequiredKeys($this->context),
      array_keys($value)
    );
    foreach ($missing_keys as $key) {
      $this->context->addViolation($constraint->message, ['@key' => $key]);
    }

  }

}
