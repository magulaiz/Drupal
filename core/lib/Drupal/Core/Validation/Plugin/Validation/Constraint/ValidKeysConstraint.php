<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that all the keys of a mapping are known.
 *
 * @Constraint(
 *   id = "ValidKeys",
 *   label = @Translation("Valid mapping keys", context = "Validation"),
 * )
 */
class ValidKeysConstraint extends Constraint {

  /**
   * The error message if an (unconditional) invalid key appears.
   *
   * @var string
   */
  public string $invalidKeyMessage = "'@key' is not a supported key.";

  /**
   * The error message if an (conditional) invalid key appears.
   *
   * @var string
   */
  public string $dynamicInvalidKeyMessage = "'@key' is an extraneous key because @condition_property_path is @condition_property_value (see config schema type @resolved_dynamic_type).";

  /**
   * The error message if the array being validated is a list.
   *
   * @var string
   */
  public string $indexedArrayMessage = 'Numerically indexed arrays are not allowed.';

  /**
   * Keys which are allowed in the validated array, or `<infer>` to auto-detect.
   *
   * @var array|string
   */
  public array|string $allowedKeys;

  /**
   * {@inheritdoc}
   */
  public function getDefaultOption() {
    return 'allowedKeys';
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions() {
    return ['allowedKeys'];
  }

}
