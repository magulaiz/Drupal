<?php

namespace Drupal\router_route_callback_secondary_test\Controller;

/**
 * Controller routines for testing the route_callbacks.
 */
class SecondaryControllerTest {

  /**
   * Test function.
   */
  public function test() {
    return ['#markup' => "test-secondary"];
  }

}
