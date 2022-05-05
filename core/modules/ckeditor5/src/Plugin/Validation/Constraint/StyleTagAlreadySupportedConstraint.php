<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Styles can only be specified for already supported tags.
 *
 * @Constraint(
 *   id = "StyleTagAlreadySupported",
 *   label = @Translation("Styles can only be specified for already supported tags.", context = "Validation"),
 * )
 *
 * @internal
 */
class StyleTagAlreadySupportedConstraint extends Constraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'A style can only be specified for already supported tags. %tag is not yet supported.';

}
