<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\ImageValidator;

/**
 * Image filepath constraint.
 *
 * @Constraint(
 *   id = "ImageFilePath",
 *   label = @Translation("Image filepath", context = "Validation"),
 *   type = { "string" }
 * )
 */
class ImageFilePathConstraint extends Image {

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
      return ImageValidator::class;
  }

}
