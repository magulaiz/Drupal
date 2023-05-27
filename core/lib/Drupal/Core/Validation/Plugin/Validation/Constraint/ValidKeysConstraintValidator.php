<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\Mapping;
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

    $invalid_keys = array_diff(
      array_keys($value),
      $constraint->getAllowedKeys($this->context)
    );

    // Allow older Drupal extensions to test on older versions of Drupal core
    // and other Drupal extensions, which means that during tests we should be
    // forgiving about keys present in the config data that the test setup's
    // config schema may not know about.
    if ($invalid_keys && self::convertViolationsToDeprecation($this->context->getRoot())) {
      trigger_error(sprintf("The '%s' configuration contains invalid keys at the property path '%s'. The following keys are either invalid or only exist in newer versions of the config schema: '%s'.",
        $this->context->getRoot()->getName(),
        $this->context->getPropertyPath(),
        implode("', '", $invalid_keys)
      ), E_USER_DEPRECATED);
      return;
    }

    foreach ($invalid_keys as $key) {
      $this->context->addViolation($constraint->invalidKeyMessage, ['@key' => $key]);
    }
  }

  /**
   * Whether violations should be mapped to a deprecation instead.
   *
   * @param \Drupal\Core\Config\Schema\Mapping $root
   *   The root of the config to check.
   *
   * @return bool
   */
  private static function convertViolationsToDeprecation(Mapping $root): bool {
    assert($root === $root->getRoot());
    $config_data = $root->getValue();
    return isset($config_data['_core']) && isset($config_data['_core']['test']) && $config_data['_core']['test'] === TRUE;
  }

}
