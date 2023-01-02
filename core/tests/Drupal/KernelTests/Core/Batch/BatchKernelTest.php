<?php

namespace Drupal\KernelTests\Core\Batch;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests batch functionality.
 *
 * @group Batch
 */
class BatchKernelTest extends KernelTestBase {

  /**
   * Tests _batch_needs_update().
   *
   * @group legacy
   */
  public function testLegacyNeedsUpdate() {
    require_once $this->root . '/core/includes/batch.inc';
    $this->expectDeprecation('_batch_needs_update() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Batch\BatchProcessorInterface::needsUpdate() instead. See https://www.drupal.org/node/3229844');
    // Before ever being called, the return value should be FALSE.
    $this->assertEquals(FALSE, _batch_needs_update());

    // Set the value to TRUE.
    $this->assertEquals(TRUE, _batch_needs_update(TRUE));
    // Check that without a parameter TRUE is returned.
    $this->assertEquals(TRUE, _batch_needs_update());

    // Set the value to FALSE.
    $this->assertEquals(FALSE, _batch_needs_update(FALSE));
    $this->assertEquals(FALSE, _batch_needs_update());
  }

  /**
   * Tests _batch_needs_update().
   */
  public function testNeedsUpdate() {
    /** @var \Drupal\Core\Batch\BatchProcessorInterface $batch_processor */
    $batch_processor = \Drupal::service('batch.processor');
    // Before ever being called, the return value should be FALSE.
    $this->assertFalse($batch_processor->needsUpdate());

    // Set the value to TRUE.
    $this->assertTrue($batch_processor->needsUpdate(TRUE));
    // Check that without a parameter TRUE is returned.
    $this->assertTrue($batch_processor->needsUpdate());

    // Set the value to FALSE.
    $this->assertFalse($batch_processor->needsUpdate(FALSE));
    $this->assertFalse($batch_processor->needsUpdate());
  }

}
