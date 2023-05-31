<?php

namespace Drupal\file\FileUsage;

use Drupal\file\FileInterface;

class ImageResizer {

 public function fileValidateImageResolution(FileInterface $file, $maximum_dimensions = 0, $minimum_dimensions = 0){
    $errors = [];

    // Check first that the file is an image.
    $image_factory = \Drupal::service('image.factory');
    $image = $image_factory->get($file->getFileUri());

    if ($image->isValid()) {
      $scaling = FALSE;
      if ($maximum_dimensions) {
        // Check that it is smaller than the given dimensions.
        [$width, $height] = explode('x', $maximum_dimensions);
        if ($image->getWidth() > $width || $image->getHeight() > $height) {
          // Try to resize the image to fit the dimensions.
          if ($image->scale($width, $height)) {
            $scaling = TRUE;
            $image->save();
            if (!empty($width) && !empty($height)) {
              $message = t(
                'The image was resized to fit within the maximum allowed dimensions of %dimensions pixels. The new dimensions of the resized image are %new_widthx%new_height pixels.',
                [
                  '%dimensions' => $maximum_dimensions,
                  '%new_width' => $image->getWidth(),
                  '%new_height' => $image->getHeight(),
                ]
              );
            } elseif (empty($width)) {
              $message = t(
                'The image was resized to fit within the maximum allowed height of %height pixels. The new dimensions of the resized image are %new_widthx%new_height pixels.',
                [
                  '%height' => $height,
                  '%new_width' => $image->getWidth(),
                  '%new_height' => $image->getHeight(),
                ]
              );
            } elseif (empty($height)) {
              $message = t(
                'The image was resized to fit within the maximum allowed width of %width pixels. The new dimensions of the resized image are %new_widthx%new_height pixels.',
                [
                  '%width' => $width,
                  '%new_width' => $image->getWidth(),
                  '%new_height' => $image->getHeight(),
                ]
              );
            }
            \Drupal::messenger()->addStatus($message);
          } else {
            $errors[] = t('The image exceeds the maximum allowed dimensions and an attempt to resize it failed.');
          }
        }
      }

      if ($minimum_dimensions) {
        // Check that it is larger than the given dimensions.
        [$width, $height] = explode('x', $minimum_dimensions);
        if ($image->getWidth() < $width || $image->getHeight() < $height) {
          if ($scaling) {
            $errors[] = t(
              'The resized image is too small. The minimum dimensions are %dimensions pixels and after resizing, the image size will be %widthx%height pixels.',
              [
                '%dimensions' => $minimum_dimensions,
                '%width' => $image->getWidth(),
                '%height' => $image->getHeight(),
              ]
            );
          } else {
            $errors[] = t(
              'The image is too small. The minimum dimensions are %dimensions pixels and the image size is %widthx%height pixels.',
              [
                '%dimensions' => $minimum_dimensions,
                '%width' => $image->getWidth(),
                '%height' => $image->getHeight(),
              ]
            );
          }
        }
      }
    }

    return $errors;
  }
 }
