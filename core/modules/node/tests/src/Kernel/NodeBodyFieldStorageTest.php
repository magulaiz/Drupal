<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests node body field storage.
 *
 * @group node
 */
class NodeBodyFieldStorageTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'system',
    'field',
    'node',
    'text',
    'filter',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Necessary for module uninstall.
    $this->installSchema('user', 'users_data');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['field', 'node']);
  }

  /**
   * Tests node body field storage persistence even if there are no instances.
   */
  public function testFieldOverrides(): void {
    $field_storage = FieldStorageConfig::loadByName('node', 'body');
    $this->assertNotEmpty($field_storage, 'Node body field storage exists.');
    $type = NodeType::create(['name' => 'Ponies', 'type' => 'ponies']);
    $type->save();
    // Ensure the 'body' field storage exists.
    $field_storage = FieldStorageConfig::loadByName('node', 'body');
    if (!$field_storage) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => 'body',
        'entity_type' => 'node',
        'type' => 'text_long',
      ]);
      $field_storage->save();
    }

    // Ensure the 'body' field exists for the 'article' content type.
    $field = FieldConfig::loadByName('node', $type->id(), 'body');
    if (!$field) {
      $field = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => $type->id(),
        'label' => 'Body',
        'settings' => [
          'display_summary' => TRUE,
          'allowed_formats' => [],
        ],
      ]);
      $field->save();
    }
    $field_storage = FieldStorageConfig::loadByName('node', 'body');
    $this->assertCount(1, $field_storage->getBundles(), 'Node body field storage is being used on the new node type.');
    $field = FieldConfig::loadByName('node', 'ponies', 'body');
    $field->delete();
    $field_storage = FieldStorageConfig::loadByName('node', 'body');
    $this->assertCount(0, $field_storage->getBundles(), 'Node body field storage exists after deleting the only instance of a field.');
    \Drupal::service('module_installer')->uninstall(['node']);
    $field_storage = FieldStorageConfig::loadByName('node', 'body');
    $this->assertNull($field_storage, 'Node body field storage does not exist after uninstalling the Node module.');
  }

}
