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
   * Optional arguments for the callback.
   *
   * @var array|null
   */
  public ?array $callbackArgs = NULL;

  /**
   * Optional arguments for the callback args.
   *
   * @var string|null
   */
  public ?string $transform = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(...$args) {
    parent::__construct(...$args);
  }

}
