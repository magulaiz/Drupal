<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Plugin\Validation\Constraint;

// cspell:ignore enableable

use Symfony\Component\Validator\Constraint;

/**
 * Styles can only be specified for already supported tags and extra classes.
 *
 * @Constraint(
 *   id = "StyleSensibleElement",
 *   label = @Translation("Styles can only be specified for already supported tags.", context = "Validation"),
 * )
 *
 * @internal
 */
class StyleSensibleElementConstraint extends Constraint {

  /**
   * When a style is defined for a tag that is not yet supported.
   *
   * @var string
   */
  public $unsupportedTagMessage = 'A style can only be specified for already supported tags. %tag is not yet supported.';

  /**
   * When a Style is defined with classes supported by an enabled plugin.
   *
   * @var string
   */
  public $conflictingEnabledPluginMessage = 'A style must only specify classes not supported by other plugins. The %classes classes on %tag are already supported by the enabled %plugin plugin.';

  /**
   * When a Style is defined with classes supported by a disabled plugin.
   *
   * @var string
   */
  public $conflictingDisabledPluginMessage = 'A style must only specify classes not supported by other plugins. The %classes classes on %tag are supported by the %plugin plugin. Remove this style and enable that plugin instead.';

}
