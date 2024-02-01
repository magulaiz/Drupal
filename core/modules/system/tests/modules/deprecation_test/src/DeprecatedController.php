<?php

namespace Drupal\deprecation_test;

/**
 * Defines a controller that calls a deprecated method.
 */
class DeprecatedController {

  /**
   * Controller callback.
   *
   * @return array
   *   Render array.
   *
   * phpcs:ignore Drupal.Commenting.Deprecated
   * @deprecated
   */
  public function deprecatedMethod() {
    return [
      '#markup' => deprecation_test_function(),
    ];
  }

}
