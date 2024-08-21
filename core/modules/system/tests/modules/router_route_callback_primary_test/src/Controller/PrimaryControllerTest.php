<?php

namespace Drupal\router_route_callback_primary_test\Controller;

/**
 * Controller routines for testing the route_callbacks.
 */
class PrimaryControllerTest {

  /**
   * Test function.
   */
  public function test() {
    return ['#markup' => "test-primary"];
  }

}
