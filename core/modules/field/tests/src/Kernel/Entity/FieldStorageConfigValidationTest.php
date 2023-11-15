<?php

namespace Drupal\Tests\field\Kernel\Entity;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;

/**
 * Tests validation of field_storage_config entities.
 *
 * @group field
 */
class FieldStorageConfigValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['field', 'node', 'user', 'file', 'image'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');

    $this->entity = FieldStorageConfig::create([
      'type' => 'boolean',
      'field_name' => 'test',
      'entity_type' => 'user',
    ]);
    $this->entity->save();
  }

  /**
   * Tests an invalid value for a plugin-specific FieldStorageConfig setting.
   */
  public function testInvalidPluginSpecificSetting(): void {
    $this->entity = FieldStorageConfig::create([
      'field_name' => 'invalid_default_image',
      'entity_type' => 'node',
      'type' => 'image',
      'settings' => [
        'default_image' => [
          'uuid' => 100000,
        ],
      ],
    ]);
    $this->assertValidationErrors([
      'settings.default_image.uuid' => 'This is not a valid UUID.',
    ]);
  }

}
