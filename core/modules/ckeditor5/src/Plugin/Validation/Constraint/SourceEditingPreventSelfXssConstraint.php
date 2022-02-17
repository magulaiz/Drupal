<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * For disallowing Source Editing configuration that allows self-XSS.
 *
 * @Constraint(
 *   id = "SourceEditingPreventSelfXssConstraint",
 *   label = @Translation("Source editing should never allow self-XSS.", context = "Validation"),
 * )
 *
 * @internal
 */
class SourceEditingPreventSelfXssConstraint extends Constraint {

  /**
   * When a Source Editing configuration is generated that enables self-XSS.
   *
   * @var string
   */
  public $message = 'ALARM: %self_xss_tags.';

}
