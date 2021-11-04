<?php

namespace Drupal\Tests\migrate\Unit\process;

use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Plugin\migrate\process\SkipOnEmpty;
use Drupal\migrate\Row;

/**
 * Tests the skip on empty process plugin.
 *
 * @group migrate
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\SkipOnEmpty
 */
class SkipOnEmptyTest extends MigrateProcessTestCase {

  /**
   * @covers ::process
   */
  public function testProcessSkipsOnEmpty() {
    $configuration['method'] = 'process';
    $this->expectException(MigrateSkipProcessException::class);
    (new SkipOnEmpty($configuration, 'skip_on_empty', []))
      ->transform('', $this->migrateExecutable, $this->row, 'destination_property');
  }

  /**
   * @covers ::process
   */
  public function testProcessBypassesOnNonEmpty() {
    $configuration['method'] = 'process';
    $value = (new SkipOnEmpty($configuration, 'skip_on_empty', []))
      ->transform(' ', $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame(' ', $value);
  }

  /**
   * @covers ::row
   */
  public function testRowSkipsOnEmpty() {
    $row = $this->prophesize(Row::class);
    $row->skip('')->shouldBeCalled();
    $configuration['method'] = 'row';
    (new SkipOnEmpty($configuration, 'skip_on_empty', []))
      ->transform('', $this->migrateExecutable, $row->reveal(), 'destination_property');
  }

  /**
   * @covers ::row
   */
  public function testRowBypassesOnNonEmpty() {
    $configuration['method'] = 'row';
    $value = (new SkipOnEmpty($configuration, 'skip_on_empty', []))
      ->transform(' ', $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame(' ', $value);
    $this->assertFalse($this->row->shouldSkip());
  }

  /**
   * Tests that a skip row exception with a message is raised.
   *
   * @covers ::row
   */
  public function testRowSkipWithMessage() {
    $configuration = [
      'method' => 'row',
      'message' => 'The value is empty',
    ];
    $row = $this->prophesize(Row::class);
    $row->skip($configuration['message'])->shouldBeCalled();
    $process = new SkipOnEmpty($configuration, 'skip_on_empty', []);
    $process->transform('', $this->migrateExecutable, $row->reveal(), 'destination_property');
  }

}
