<?php

namespace Drupal\KernelTests\Config;

use Drupal\Core\Config\MemoryStorage;
use Drupal\Core\Config\StorageComparer;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class StorageComparerKernelTest.
 *
 * @group config
 */
class StorageComparerKernelTest extends KernelTestBase {

  /**
   * Test how the StorageComparer can be serialized.
   */
  public function testSerialization() {
    $active = $this->container->get('config.storage');
    $active->createCollection('test')->write('test.test', ['label' => 'new collection']);
    // The export storage contains the same data as the active storage but is
    // decorated with a class which does not use DependencySerializationTrait.
    $export = $this->container->get('config.storage.export');
    $target = new MemoryStorage();

    $comparer = new StorageComparer($export, $target);
    $comparer->createChangelist();

    $serialized = serialize($comparer);

    /** @var StorageComparer $unserialized */
    $unserialized = unserialize($serialized);

    $this->assertEquals($comparer->getChangelist(), $unserialized->getChangelist());
    $this->assertEquals($comparer->getAllCollectionNames(), $unserialized->getAllCollectionNames());
    $this->assertEquals($comparer->getEmptyChangelist(), $unserialized->getEmptyChangelist());
  }
}
