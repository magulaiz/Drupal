<?php

namespace Drupal\asset_test\Controller;

/**
 * Provides route to test asset loading.
 */
class AssetTestController {

  /**
   * Page with loading of libraries - 1.
   *
   * @return array
   *   Renderable array with libraries loaded.
   */
  public function loadLibraries1() {
    return [
      '#attached' => [
        'library' => [
          'asset_test/css_dep_header_b',
          'asset_test/dep_header_a',
          'asset_test/dep_header_preprocess_a',
          'asset_test/dep_header_b',
          'asset_test/dep_header_preprocess_b',
          'asset_test/validation',
        ],
      ],
      'content' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => 'Check the JavaScript console to validate if libraries are loaded.',
      ],
    ];
  }

  /**
   * Page with loading of libraries - 2.
   *
   * @return array
   *   Renderable array with libraries loaded.
   */
  public function loadLibraries2() {
    return [
      '#attached' => [
        'library' => [
          'asset_test/dep_header_a',
          'asset_test/dep_header_preprocess_a',
          'asset_test/dep_header_b',
          'asset_test/dep_header_preprocess_b',
          'asset_test/css_dep_header_b',
          'asset_test/validation',
        ],
      ],
      'content' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => 'Check the JavaScript console to validate if libraries are loaded.',
      ],
    ];
  }

}
