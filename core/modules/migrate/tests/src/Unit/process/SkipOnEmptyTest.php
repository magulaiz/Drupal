<?php

declare(strict_types=1);

namespace Drupal\Tests\migrate\Unit\process;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\migrate\process\SkipOnEmpty;

/**
 * Tests the skip on empty process plugin.
 *
 * @group migrate
 */
#[CoversClass(\Drupal\migrate\Plugin\migrate\process\SkipOnEmpty::class)]
class SkipOnEmptyTest extends MigrateProcessTestCase {

  public function testProcessSkipsOnEmpty() {
    $configuration['method'] = 'process';
    $plugin = new SkipOnEmpty($configuration, 'skip_on_empty', []);
    $this->assertFalse($plugin->isPipelineStopped());
    $plugin->transform('', $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertTrue($plugin->isPipelineStopped());
  }

  public function testProcessBypassesOnNonEmpty() {
    $configuration['method'] = 'process';
    $plugin = new SkipOnEmpty($configuration, 'skip_on_empty', []);
    $value = $plugin
      ->transform(' ', $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame(' ', $value);
    $this->assertFalse($plugin->isPipelineStopped());
  }

  public function testRowSkipsOnEmpty() {
    $configuration['method'] = 'row';
    $this->expectException(MigrateSkipRowException::class);
    (new SkipOnEmpty($configuration, 'skip_on_empty', []))
      ->transform('', $this->migrateExecutable, $this->row, 'destination_property');
  }

  public function testRowBypassesOnNonEmpty() {
    $configuration['method'] = 'row';
    $value = (new SkipOnEmpty($configuration, 'skip_on_empty', []))
      ->transform(' ', $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame(' ', $value);
  }

  /**
   * Tests that a skip row exception without a message is raised.
   */
  public function testRowSkipWithoutMessage() {
    $configuration = [
      'method' => 'row',
    ];
    $process = new SkipOnEmpty($configuration, 'skip_on_empty', []);
    $this->expectException(MigrateSkipRowException::class);
    $process->transform('', $this->migrateExecutable, $this->row, 'destination_property');
  }

  /**
   * Tests that a skip row exception with a message is raised.
   */
  public function testRowSkipWithMessage() {
    $configuration = [
      'method' => 'row',
      'message' => 'The value is empty',
    ];
    $process = new SkipOnEmpty($configuration, 'skip_on_empty', []);
    $this->expectException(MigrateSkipRowException::class);
    $this->expectExceptionMessage('The value is empty');
    $process->transform('', $this->migrateExecutable, $this->row, 'destination_property');
  }

  /**
   * Tests repeated execution of a process plugin can reset the pipeline stoppage correctly.
   */
  public function testMultipleTransforms() {
    $configuration['method'] = 'process';
    $plugin = new SkipOnEmpty($configuration, 'skip_on_empty', []);

    // Confirm transform will stop the pipeline.
    $value = $plugin
      ->transform('', $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertNull($value);
    $this->assertTrue($plugin->isPipelineStopped());

    // Restart the pipeline and test again.
    $plugin->reset();
    $this->assertFalse($plugin->isPipelineStopped());
    $value = $plugin
      ->transform(' ', $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame(' ', $value);
    $this->assertFalse($plugin->isPipelineStopped());
  }

}
