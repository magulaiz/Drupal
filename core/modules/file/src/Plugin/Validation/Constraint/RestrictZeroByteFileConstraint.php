<?php

namespace Drupal\file\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Zero Byte File constraint.
 *
 * @Constraint(
 *   id = "RestrictZeroByteFile",
 *   label = @Translation("Restrict zero byte file", context = "Validation"),
 *   type = "file"
 * )
 */
class RestrictZeroByteFileConstraint extends Constraint {

  /**
   * The message for when a empty file is uploaded.
   *
   * @var string
   */
  public string $zeroByteFileMessage = 'The uploaded is empty! Please upload a valid file.';

}
