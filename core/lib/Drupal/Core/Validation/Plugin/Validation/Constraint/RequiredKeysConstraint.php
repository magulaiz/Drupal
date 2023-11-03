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
   * The error message if a dynamically required key is missing.
   *
   * @var string
   */
  public string $dynamicMessage = "'@key' is a required key because @dynamic_type_property_path is @dynamic_type_property_value (see config schema type @resolved_dynamic_type).";

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
