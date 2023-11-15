<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * No Markup constraint.
 *
 * @Constraint(
 *   id = "NoMarkup",
 *   label = @Translation("No Markup", context = "Validation"),
 *   type = { "string" }
 * )
 */
class NoMarkupConstraint extends Constraint {

  /**
   * The error message if an the value contains markup.
   *
   * @var string
   */
  public string $markupPresentMessage = 'The value should not contain HTML markup';

}
