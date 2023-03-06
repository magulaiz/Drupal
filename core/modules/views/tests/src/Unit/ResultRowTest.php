<?php

namespace Drupal\Tests\views\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\views\ResultRow;

/**
 * @coversDefaultClass \Drupal\views\ResultRow
 * @group views
 */
class ResultRowTest extends UnitTestCase {

  /**
   * A views' result row.
   */
  protected ResultRow $row;

  protected function setUp(): void {
    parent::setUp();
    $this->row = new ResultRow([
      'index' => 1,
      'data' => (object) [
        'alpha' => 'foo',
        'beta' => 'bar',
      ],
    ]);
  }

  /**
   * @covers ::__construct
   * @covers ::__set
   * @covers ::__get
   * @covers ::__isset
   * @covers ::__unset
   */
  public function testMagic(): void {
    $this->assertTrue(isset($this->row->index));
    $this->assertTrue(isset($this->row->alpha));
    $this->assertTrue(isset($this->row->beta));
    $this->assertFalse(isset($this->row->getData()->index));
    $this->assertTrue(isset($this->row->getData()->alpha));
    $this->assertTrue(isset($this->row->getData()->beta));
    $this->assertSame(1, $this->row->index);
    $this->assertSame('foo', $this->row->alpha);
    $this->assertSame('bar', $this->row->beta);
    $this->assertSame('foo', $this->row->getData()->alpha);
    $this->assertSame('bar', $this->row->getData()->beta);
    unset($this->row->beta);
    $this->assertFalse(isset($this->row->beta));
    $this->assertFalse(isset($this->row->getData()->beta));
    $this->row->charlie = 'baz';
    $this->assertTrue(isset($this->row->charlie));
    $this->assertTrue(isset($this->row->getData()->charlie));
    $this->assertSame('baz', $this->row->charlie);
    $this->assertSame('baz', $this->row->getData()->charlie);
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage("Property zulu does not exist");
    $value = $this->row->zulu;
  }

}
