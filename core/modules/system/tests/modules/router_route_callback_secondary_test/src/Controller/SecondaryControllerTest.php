<?php

namespace Drupal\router_route_callback_secondary_test\Controller;

use PHPUnit\Framework\TestCase;

/**
 * Controller routines for testing the route_callbacks.
 */
class SecondaryControllerTest extends TestCase {

  /**
   * Test function.
   */
  public function test() {
    return ['#markup' => "test-secondary"];
  }

}
