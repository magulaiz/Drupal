<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Length constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @todo: Move this below the TypedData core component.
 */
#[Constraint(
  id: 'Length',
  label: new TranslatableMarkup('Length', [], ['context' => 'Validation']),
  type: ['string']
)]
class LengthConstraint extends Length {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $options = NULL) {
    $options['maxMessage'] ??= 'This value is too long. It should have %limit character or less.|This value is too long. It should have %limit characters or less.';
    $options['minMessage'] ??= 'This value is too short. It should have %limit character or more.|This value is too short. It should have %limit characters or more.';
    $options['exactMessage'] ??= 'This value should have exactly %limit character.|This value should have exactly %limit characters.';
    parent::__construct($options);
  }

  /**
   * {@inheritdoc}
   */
  public function validatedBy(): string {
    return '\Symfony\Component\Validator\Constraints\LengthValidator';
  }

}
