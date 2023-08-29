<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that all the required keys of a mapping are present.
 *
 * @Constraint(
 *   id = "RequiredKeys",
 *   label = @Translation("Required mapping keys", context = "Validation"),
 * )
 */
class RequiredKeysConstraint extends Constraint {

  /**
   * The error message if a key is missing.
   *
   * @var string
   */
  public string $message = "'@key' is a required key.";

  /**
   * The error message if a conditionally required key is missing.
   *
   * @var string
   */
  public string $conditionalMessage = "'@key' is a conditionally required key because @condition_property_path is @condition_property_value (see config schema type @resolved_dynamic_type).";

  /**
   * The error message if a key is extraneous.
   *
   * @var string
   */
  public string $extraneousMessage = "'@key' is an extraneous key because @condition_property_path is @condition_property_value (see config schema type @resolved_dynamic_type).";

  /**
   * Keys which are required — only `<infer>` supported currently.
   *
   * @var string
   */
  public string $requiredKeys;

  /**
   * {@inheritdoc}
   */
  public function getDefaultOption() {
    return 'requiredKeys';
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions() {
    return ['requiredKeys'];
  }

}
