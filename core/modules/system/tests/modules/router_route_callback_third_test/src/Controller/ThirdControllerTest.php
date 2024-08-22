<?php

namespace Drupal\router_route_callback_third_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for testing the route_callbacks.
 */
class ThirdControllerTest extends ControllerBase {

  /**
   * Test function.
   */
  public function test() {
    return ['#markup' => "test-third"];
  }

}
