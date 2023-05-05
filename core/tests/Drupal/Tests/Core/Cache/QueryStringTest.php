<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Cache;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\QueryString;
use Drupal\Core\State\StateInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the query string class.
 *
 * @coversDefaultClass \Drupal\Core\Cache\QueryString
 */
class QueryStringTest extends UnitTestCase {

  /**
   * @covers ::reset
   * @covers ::get
   */
  public function testReset(): void {
    $state = $this->createMock(StateInterface::class);
    $time = $this->createMock(TimeInterface::class);
    $queryString = new QueryString($state, $time);
    $queryString->reset();
  }

}
