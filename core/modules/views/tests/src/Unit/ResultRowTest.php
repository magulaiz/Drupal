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
      'data' => [
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
   * @covers ::hasColumn
   */
  public function testMagic(): void {
    $this->assertTrue(isset($this->row->index));
    $this->assertTrue(isset($this->row->alpha));
    $this->assertTrue(isset($this->row->beta));
    $this->assertFalse($this->row->hasColumn('index'));
    $this->assertTrue($this->row->hasColumn('alpha'));
    $this->assertTrue($this->row->hasColumn('beta'));
    $this->assertSame(1, $this->row->index);
    $this->assertSame('foo', $this->row->alpha);
    $this->assertSame('bar', $this->row->beta);
    unset($this->row->beta);
    $this->assertFalse(isset($this->row->beta));
    $this->assertFalse($this->row->hasColumn('beta'));
    $this->row->charlie = 'baz';
    $this->assertTrue(isset($this->row->charlie));
    $this->assertTrue($this->row->hasColumn('charlie'));
    $this->assertSame('baz', $this->row->charlie);
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage("Column 'zulu' does not exist");
    $value = $this->row->zulu;
  }

}
