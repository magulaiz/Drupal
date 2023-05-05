<?php

namespace Drupal\KernelTests\Core\Cache;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\QueryString;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the query string cache functionality.
 *
 * @group Cache
 * @coversDefaultClass \Drupal\Core\Cache\QueryString
 */
class QueryStringTest extends KernelTestBase {

  /**
   * Tests resetting and getting the query string value.
   */
  public function testResetGet(): void {
    $state = $this->container->get('state');
    // Return a fixed timestamp.
    $time = $this->createStub(TimeInterface::class);
    $time->method('getRequestTime')
      ->willReturn(1683246590);

    $queryString = new QueryString($state, $time);

    $queryString->reset();
    $value = $queryString->get();

    $this->assertEquals('ru5tdq', $value);
  }

}
