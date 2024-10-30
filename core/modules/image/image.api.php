<?php

/**
 * @file
 * Hooks related to image styles and effects.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the information provided in \Drupal\image\Annotation\ImageEffect.
 *
 * @param $effects
 *   The array of image effects, keyed on the machine-readable effect name.
 */
function hook_image_effect_info_alter(&$effects) {
  // Override the Image module's 'Scale and Crop' effect label.
  $effects['image_scale_and_crop']['label'] = t('Bangers and Mash');
}

/**
 * Respond to image style flushing.
 *
 * This hook enables modules to take effect when a style is being flushed (all
 * images are being deleted from the server and regenerated). Any
 * module-specific caches that contain information related to the style should
 * be cleared using this hook. This hook is called whenever a style is updated,
 * deleted, or any effect associated with the style is update or deleted.
 *
 * @param \Drupal\image\ImageStyleInterface $style
 *   The image style object that is being flushed.
 * @param string|null $path
 *   (optional) The original image path or URI. If it's supplied, only this
 *   image derivative will be flushed.
 */
function hook_image_style_flush($style, $path = NULL) {
  // Empty cached data that contains information about the style.
  \Drupal::cache('my_module')->deleteAll();
}

/**
 * Respond to image derivative creation.
 *
 * @param string $original_uri
 *   URI of original image.
 * @param string $style
 *   ID of the style.
 * @param string $derivative_uri
 *   URI of created derivative.
 */
function hook_image_derivative_created(string $original_uri, string $style, string $derivative_uri) {
  // Notify a remote server that a derivative has been created.
  if ($style === 'spa_header') {
    $post_data = [
      'original_uri' => $original_uri,
      'derivative_uri' => $derivative_uri,
    ];
    $http_client = \Drupal::httpClient();
    $http_client->post('https://example.com/my-image-consumer-service', [
      'form_params' => $post_data,
    ]);
  }
}

/**
 * @} End of "addtogroup hooks".
 */
