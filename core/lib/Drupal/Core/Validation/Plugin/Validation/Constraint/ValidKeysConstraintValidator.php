<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\Mapping;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the ValidKeys constraint.
 */
class ValidKeysConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    assert($constraint instanceof ValidKeysConstraint);

    if (!is_array($value)) {
      throw new UnexpectedTypeException($value, 'array');
    }

    // Indexed arrays are invalid by definition. array_is_list() returns TRUE
    // for empty arrays, so only do this check if $value is not empty.
    if ($value && array_is_list($value)) {
      $this->context->addViolation($constraint->indexedArrayMessage);
      return;
    }

    if ($constraint->allowedKeys === '<infer>') {
      $mapping = $this->context->getObject();
      assert($mapping instanceof Mapping);
      $valid_keys = $mapping->getValidKeys();
      $conditionally_valid_keys = array_merge(...array_values($mapping->getConditionallyValidKeys()));
      $other_type_valid_keys = array_diff($conditionally_valid_keys, $valid_keys);

      // Unconditionally valid: valid here and not conditionally valid.
      $invalid_keys = array_diff(array_keys($value), $valid_keys, $other_type_valid_keys);
      foreach ($invalid_keys as $key) {
        $this->context->addViolation($constraint->invalidKeyMessage, ['@key' => $key]);
      }

      // Conditionally valid: not valid here but valid elsewhere.
      $dynamic_invalid_keys = array_intersect(array_keys($value), $other_type_valid_keys);
      foreach ($dynamic_invalid_keys as $key) {
        $this->context->addViolation($constraint->dynamicInvalidKeyMessage, ['@key' => $key] + RequiredKeysConstraintValidator::getConditionalMessageParameters($mapping));
      }
    }
    elseif (is_array($constraint->allowedKeys)) {
      $invalid_keys = array_diff(array_keys($value), $constraint->allowedKeys);
      foreach ($invalid_keys as $key) {
        $this->context->addViolation($constraint->invalidKeyMessage, ['@key' => $key]);
      }
    }
    else {
      throw new InvalidArgumentException("'$constraint->allowedKeys' is not a valid set of allowed keys.");
    }
  }

}
