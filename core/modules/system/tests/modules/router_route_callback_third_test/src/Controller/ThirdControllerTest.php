<?php

namespace Drupal\router_route_callback_third_test\Controller;

use PHPUnit\Framework\TestCase;

/**
 * Controller routines for testing the route_callbacks.
 */
class ThirdControllerTest extends TestCase {

  /**
   * Test function.
   */
  public function test() {
    return ['#markup' => "test-third"];
  }

}
