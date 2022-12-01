<?php

declare(strict_types = 1);

namespace Drupal\Core\Config\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if config entity properties have been changed.
 *
 * @Constraint(
 *   id = "ImmutableFields",
 *   label = @Translation("Fields are unchanged", context = "Validation"),
 *   type = { "entity" }
 * )
 */
class ImmutableFieldsConstraint extends Constraint {

  public string $message = "The '@name' property cannot be changed.";

  public array $fields = [];

  public function getDefaultOption() {
    return 'fields';
  }

  public function getRequiredOptions() {
    return ['fields'];
  }

}
