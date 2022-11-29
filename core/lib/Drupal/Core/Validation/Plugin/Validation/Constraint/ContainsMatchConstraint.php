<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that an array contains a value that matches a regular expression.
 *
 * @Constraint(
 *   id = "ContainsMatch",
 *   label = @Translation("Contains matching value", context = "Validation")
 * )
 */
class ContainsMatchConstraint extends Constraint {

  /**
   * The regular expression to find matching values.
   *
   * @var string
   */
  public string $pattern;

  /**
   * The error message if no matching values are found.
   *
   * @var string
   */
  public string $message = 'Does not contain a value matching "@pattern".';

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions() {
    return ['pattern'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOption() {
    return 'pattern';
  }

}
