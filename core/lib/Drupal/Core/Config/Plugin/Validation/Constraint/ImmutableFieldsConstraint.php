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

  /**
   * The error message if an immutable property has been changed.
   *
   * @var string
   */
  public string $message = "The '@name' property cannot be changed.";

  /**
   * The names of the immutable fields.
   *
   * @var string[]
   */
  public array $fields = [];

  /**
   * {@inheritdoc}
   */
  public function getDefaultOption() {
    return 'fields';
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions() {
    return ['fields'];
  }

}
