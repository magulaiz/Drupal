<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraints\Choice;

/**
 * Provide a set of valid choices.
 */
#[Constraint(
  id: 'Choice',
  label: new TranslatableMarkup('Choice', [], ['context' => 'Validation'])
)]
class ChoiceConstraint extends Choice {

  /**
   * {@inheritdoc}
   */
  public function __construct(...$args) {
    $this->message = "%value is not a valid choice.";
    parent::__construct(...$args);
  }

}
