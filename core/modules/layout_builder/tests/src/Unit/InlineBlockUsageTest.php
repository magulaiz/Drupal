<?php

declare(strict_types=1);

namespace Drupal\Tests\layout_builder\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Core\Database\Connection;
use Drupal\layout_builder\InlineBlockUsage;
use Drupal\Tests\UnitTestCase;

/**
 * @group layout_builder
 */
#[CoversClass(\Drupal\layout_builder\InlineBlockUsage::class)]
class InlineBlockUsageTest extends UnitTestCase {

  /**
   * Tests calling deleteUsage() with empty array.
   */
  public function testEmptyDeleteUsageCall() {
    $connection = $this->prophesize(Connection::class);
    $connection->delete('inline_block_usage')->shouldNotBeCalled();

    (new InlineBlockUsage($connection->reveal()))->deleteUsage([]);
  }

}
