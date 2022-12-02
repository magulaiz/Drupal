<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

/**
 * @Constraint(
 *   id = "MachineName",
 *   label = @Translation("A valid machine name", context = "Validation")
 * )
 *
 * Note: this does not check uniqueness, only the format. Add an additional
 * constraint to ensure unique machine names; how to do that depends on the
 * configuration. For config entities, the `UniqueField` is usually possible.
 *
 * @see \Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldConstraint
 */
class MachineNameConstraint extends RegexConstraint {

  /**
   * {@inheritdoc}
   */
  public $message = 'Invalid machine name.';

  /**
   * The pattern to use.
   *
   * @var string
   *
   * @see \Drupal\Core\Render\Element\MachineName::validateMachineName
   */
  const PATTERN = '/^[a-z0-9_]+$/';

  /**
   * {@inheritdoc}
   */
  public function __construct(array|string|null $pattern, ...$arguments) {
    $pattern = self::PATTERN;
    parent::__construct($pattern, ...$arguments);
  }

}
