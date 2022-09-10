<?php

namespace Drupal\KernelTests\Core\Entity;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Provides tests for the Entity Field Manager.
 *
 * @coversDefaultClass \Drupal\Core\Entity\EntityFieldManager
 *
 * @group Entity
 */
class EntityFieldManagerTest extends EntityKernelTestBase {

  /**
   * The bundle field map key/value store.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $bundleFieldMap;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->bundleFieldMap = $this->container->get('keyvalue')
      ->get('entity.definitions.bundle_field_map');
  }

  /**
   * Tests rebuilding the bundle field map.
   *
   * @covers ::rebuildBundleFieldMap
   */
  public function testRebuildBundleFieldMap() {
    // Set up some fields on the 'entity_test' and 'user' entity types.
    entity_test_create_bundle('bundle1');
    entity_test_create_bundle('bundle2');
    foreach (['entity_test', 'user'] as $entity_type_id) {
      FieldStorageConfig::create([
        'field_name' => 'test_field',
        'type' => 'text',
        'entity_type' => $entity_type_id,
      ])->save();
    }
    FieldConfig::create([
      'field_name' => 'test_field',
      'entity_type' => 'entity_test',
      'bundle' => 'bundle1',
    ])->save();
    FieldConfig::create([
      'field_name' => 'test_field',
      'entity_type' => 'entity_test',
      'bundle' => 'bundle2',
    ])->save();
    FieldConfig::create([
      'field_name' => 'test_field',
      'entity_type' => 'user',
      'bundle' => 'user',
    ])->save();

    $original_map_data = $this->bundleFieldMap->getAll();
    $this->bundleFieldMap->deleteAll();

    $this->container->get('entity_field.manager')->rebuildBundleFieldMap();

    $new_map_data = $this->bundleFieldMap->getAll();
    $this->assertIsArray($original_map_data);
    $this->assertIsArray($new_map_data);
    // There's no guarantee the field map data will be in the same order as it
    // was, but it should otherwise be the same, so assert on equality and not
    // identity.
    $this->assertEquals($original_map_data, $new_map_data, 'The rebuilt bundle field map matches the original one.');
  }

}
