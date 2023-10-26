<?php

namespace Drupal\image;

use Drupal\Core\Image\ImageFactory;

/**
 * Provides a trait to create image upload validators from field settings.
 */
trait ImageValidatorSettingsTrait {

  /**
   * Gets the image upload validators for the given field settings.
   *
   * @param array $settings
   *   An associative array of settings. The following keys are supported:
   *     - min_resolution: The minimum resolution allowed for uploaded images.
   *     - max_resolution: The maximum resolution allowed for uploaded images.
   *     - file_extensions: A space-separated list of allowed file extensions.
   *
   * @return array
   *   An array suitable for passing to file_save_upload() or the file field
   *   element's '#upload_validators' property.
   */
  public function getImageUploadValidators(array $settings): array {
    // Add image validation.
    $validators['FileIsImage'] = [];

    // Add upload resolution validation.
    if ($settings['max_resolution'] || $settings['min_resolution']) {
      $validators['FileImageDimensions'] = [
        'maxDimensions' => $settings['max_resolution'],
        'minDimensions' => $settings['min_resolution'],
      ];
    }

    $extensions = $settings['file_extensions'];
    $supported_extensions = $this->getImageFactory()->getSupportedExtensions();

    // If using custom extension validation, ensure that the extensions are
    // supported by the current image toolkit. Otherwise, validate against all
    // toolkit supported extensions.
    $extensions = !empty($extensions) ? array_intersect(explode(' ', $extensions), $supported_extensions) : $supported_extensions;
    $validators['FileExtension']['extensions'] = implode(' ', $extensions);

    return $validators;
  }

  /**
   * Gets the image factory.
   */
  protected function getImageFactory(): ImageFactory {
    if (!isset($this->imageFactory)) {
      $this->imageFactory = \Drupal::service('image.factory');
    }
    return $this->imageFactory;
  }

}
