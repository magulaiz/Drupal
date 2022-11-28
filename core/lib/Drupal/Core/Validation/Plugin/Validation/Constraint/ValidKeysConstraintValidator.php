<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the ValidKeys constraint.
 */
class ValidKeysConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    if (!is_array($value)) {
      throw new UnexpectedTypeException($value, 'array');
    }

    // Indexed arrays are invalid by definition.
    if (array_is_list($value)) {
      $this->context->addViolation($constraint->indexedArrayMessage);
      return;
    }

    foreach (array_keys($value) as $key) {
      if (in_array($key, $constraint->allowedKeys, TRUE)) {
        continue;
      }
      $this->context->addViolation($constraint->invalidKeyMessage, ['@key' => $key]);
    }
  }

}
