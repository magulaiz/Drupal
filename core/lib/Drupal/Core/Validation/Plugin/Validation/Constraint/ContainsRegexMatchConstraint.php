<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Constraint(
 *   id = "ContainsRegexMatch",
 *   label = @Translation("Contains value matching expression", context = "Validation")
 * )
 */
class ContainsRegexMatchConstraint extends Constraint {

  public string $pattern;

  public string $message = 'Does not contain a value matching "@pattern".';

  public function getRequiredOptions() {
    return ['pattern'];
  }

  public function getDefaultOption() {
    return 'pattern';
  }

}
