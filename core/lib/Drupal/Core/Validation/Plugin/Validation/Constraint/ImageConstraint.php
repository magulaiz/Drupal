<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\Image;

/**
 * Image constraint.
 *
 * @Constraint(
 *   id = "Image",
 *   label = @Translation("Image", context = "Validation"),
 *   type = { "string" }
 * )
 */
class ImageConstraint extends Image {

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\ImageValidator';
  }

}
