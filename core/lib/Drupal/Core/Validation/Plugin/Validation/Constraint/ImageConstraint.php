<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraints\Image;

/**
 * Image constraint.
 *
 * Overrides the symfony constraint to use Drupal-style replacement patterns.
 *
 * @Constraint(
 *   id = "Image",
 *   label = @Translation("Image", context = "Validation"),
 *   type = false
 * )
 */
class ImageConstraint extends Image {


  public $maxWidthMessage = 'The image width is too big (%width px). Allowed maximum width is %max_width px.';
  public $minWidthMessage = 'The image width is too small (%width px). Minimum width expected is %min_width px.';
  public $maxHeightMessage = 'The image height is too big (%height px). Allowed maximum height is %max_height px.';
  public $minHeightMessage = 'The image height is too small (%height px). Minimum height expected is %min_height px.';
  public $minPixelsMessage = 'The image has too few pixels (%pixels pixels). Minimum amount expected is %min_pixels pixels.';
  public $maxPixelsMessage = 'The image has too many pixels (%pixels pixels). Maximum amount expected is %max_pixels pixels.';
  public $maxRatioMessage = 'The image ratio is too big (%ratio). Allowed maximum ratio is %max_ratio.';
  public $minRatioMessage = 'The image ratio is too small (%ratio). Minimum ratio expected is %min_ratio.';
  public $allowSquareMessage = 'The image is square (%width x  %height px). Square images are not allowed.';
  public $allowLandscapeMessage = 'The image is landscape oriented (%width x %height px). Landscape oriented images are not allowed.';
  public $allowPortraitMessage = 'The image is portrait oriented (%width x %height px). Portrait oriented images are not allowed.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Symfony\Component\Validator\Constraints\ImageValidator';
  }

}
