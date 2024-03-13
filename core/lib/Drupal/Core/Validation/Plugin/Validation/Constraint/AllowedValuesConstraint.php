<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraints\Choice;

/**
 * Checks for the value being allowed.
 *
 * @see \Drupal\Core\TypedData\OptionsProviderInterface
 */
#[Constraint(
  id: 'AllowedValues',
  label: new TranslatableMarkup('Allowed values', [], ['context' => 'Validation'])
)]
class AllowedValuesConstraint extends Choice {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $options = NULL) {
    $options['strict'] ??= TRUE;
    $options['minMessage'] ??= 'You must select at least %limit choice.|You must select at least %limit choices.';
    $options['maxMessage'] ??= 'You must select at most %limit choice.|You must select at most %limit choices.';
    parent::__construct($options);
  }

}
