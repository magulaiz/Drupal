<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraints\Count;

/**
 * Count constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 */
#[Constraint(
  id: 'Count',
  label: new TranslatableMarkup('Count', [], ['context' => 'Validation']),
  type: ['list']
)]
class CountConstraint extends Count {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $options = NULL) {
    $options['minMessage'] ??= 'This collection should contain %limit element or more.|This collection should contain %limit elements or more.';
    $options['maxMessage'] ??= 'This collection should contain %limit element or less.|This collection should contain %limit elements or less.';
    $options['exactMessage'] ??= 'This collection should contain exactly %limit element.|This collection should contain exactly %limit elements.';
    parent::__construct($options);
  }

  /**
   * {@inheritdoc}
   */
  public function validatedBy(): string {
    return '\Symfony\Component\Validator\Constraints\CountValidator';
  }

}
