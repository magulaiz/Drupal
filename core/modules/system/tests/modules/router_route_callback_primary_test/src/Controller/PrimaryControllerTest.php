<?php

namespace Drupal\router_route_callback_primary_test\Controller;

use PHPUnit\Framework\TestCase;

/**
 * Controller routines for testing the route_callbacks.
 */
class PrimaryControllerTest extends TestCase {

  /**
   * Test function.
   */
  public function test() {
    return ['#markup' => "test-primary"];
  }

}
