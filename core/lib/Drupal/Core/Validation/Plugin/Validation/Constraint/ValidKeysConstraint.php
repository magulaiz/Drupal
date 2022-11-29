<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that all the keys of an array are known.
 *
 * @Constraint(
 *   id = "ValidKeys",
 *   label = @Translation("Valid array keys", context = "Validation")
 * )
 */
class ValidKeysConstraint extends Constraint {

  /**
   * The error message if an invalid key appears.
   *
   * @var string
   */
  public string $invalidKeyMessage = "'@key' is not a supported key.";

  /**
   * The error message if the array being validated is a list.
   *
   * @var string
   */
  public string $indexedArrayMessage = 'Numerically indexed arrays are not allowed.';

  /**
   * The keys which are allowed to be present in the validated array.
   *
   * @var array
   */
  public array $allowedKeys = [];

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
