<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Cache;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\ChainedFastBackend;
use Drupal\Core\Cache\DatabaseBackend;
use Drupal\Core\Cache\PhpBackend;

/**
 * Unit test of the fast chained backend using the generic cache unit test base.
 *
 * @group Cache
 */
class ChainedFastBackendTest extends GenericCacheBackendUnitTestBase {

  /**
   * Creates a new instance of ChainedFastBackend.
   *
   * @return \Drupal\Core\Cache\ChainedFastBackend
   *   A new ChainedFastBackend object.
   */
  protected function createCacheBackend($bin): DatabaseBackend {
    $consistent_backend = new DatabaseBackend(\Drupal::service('database'), \Drupal::service('cache_tags.invalidator.checksum'), $bin, \Drupal::service('serialization.phpserialize'), \Drupal::service(TimeInterface::class), 100);
    $fast_backend = new PhpBackend($bin, \Drupal::service('cache_tags.invalidator.checksum'), \Drupal::service(TimeInterface::class));
    $backend = new ChainedFastBackend($consistent_backend, $fast_backend, $bin);
    // Explicitly register the cache bin as it can not work through the
    // cache bin list in the container.
    \Drupal::service('cache_tags.invalidator')->addInvalidator($backend);
    return $backend;
  }

  /**
   * Tests that lastWriteTimestamp works as expected.
   *
   * @dataProvider dataLastWriteTimestamp
   */
  public function testLastWriteTimestamp($value_one, $value_two): void {
    $bin = 'test';
    $consistent_backend = new DatabaseBackend(\Drupal::service('database'), \Drupal::service('cache_tags.invalidator.checksum'), $bin, 100, \Drupal::service('request_stack'), \Drupal::service('datetime.time'));
    $fast_backend = new PhpBackend($bin, \Drupal::service('cache_tags.invalidator.checksum'), \Drupal::service('datetime.time'));
    $backend = new MarkAsOutdatedChainedFastBackend($consistent_backend, $fast_backend, $bin);
    // Explicitly register the cache bin as it can not work through the
    // cache bin list in the container.
    \Drupal::service('cache_tags.invalidator')->addInvalidator($backend);

    $backend->delete('test1');
    $backend->set('test1', $value_one);
    $item1 = $backend->get('test1');
    $consistent_backend->set('test1', $value_two);
    // Set the lastWriteTimestamp to the timestamp of the first entry.
    $backend->setLastWriteTimestamp($item1->created);
    $item2 = $backend->get('test1');
    // We expect the value from the consistent cache.
    $this->assertSame($value_two, $item2->data);
  }

  /**
   * Data provider for dataLastWriteTimestamp.
   */
  public static function dataLastWriteTimestamp(): array {
    $data = [];
    for ($i = 0; $i < 100; $i++) {
      $data['run ' . $i] = ['value1', 'value2'];
    }
    return $data;
  }

}

/**
 * Version of ChainedFastBackend that allows settings of getLastWriteTimestamp.
 *
 * @package Drupal\KernelTests\Core\Cache
 */
class MarkAsOutdatedChainedFastBackend extends ChainedFastBackend {

  /**
   * Sets the lastWriteTimestamp.
   */
  public function setLastWriteTimestamp(float $timestamp): void {
    $this->lastWriteTimestamp = $timestamp + 0.01;
  }

}
