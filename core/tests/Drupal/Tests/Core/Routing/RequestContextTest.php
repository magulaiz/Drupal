<?php

namespace Drupal\Tests\Core\Routing;

use Drupal\Core\Routing\RequestContext;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\Core\Routing\RequestContext
 * @group Routing
 */
class RequestContextTest extends UnitTestCase {

  /**
   * Make sure there is no TypeError when passing an empty RequestStack.
   *
   * @covers ::fromRequestStack
   */
  public function testFromRequestStack() {
    $request_context = new RequestContext();
    $request_stack = new RequestStack();
    $request_context->fromRequestStack($request_stack);
    $this->assertIsObject($request_context);
  }

}
