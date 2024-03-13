<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Regex constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 */
#[Constraint(
  id: 'Regex',
  label: new TranslatableMarkup('Regex', [], ['context' => 'Validation'])
)]
class RegexConstraint extends Regex {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $options = NULL) {
    $options['message'] ??= 'This value is not valid.';
    parent::__construct($options);
  }

  /**
   * {@inheritdoc}
   */
  public function validatedBy(): string {
    return '\Symfony\Component\Validator\Constraints\RegexValidator';
  }

}
