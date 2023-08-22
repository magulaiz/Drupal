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
  protected static $modules = ['field', 'node', 'user'];

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
   * Tests that immutable fields cannot be changed.
   *
   * @param string $property
   *   The name of the immutable property.
   * @param mixed $value
   *   The value to set for the property.
   *
   * @testWith ["entity_type", "node"]
   *   ["field_name", "field_test_changed"]
   *   ["type", "integer"]
   */
  public function testImmutableFields(string $property, mixed $value): void {
    $this->entity->set($property, $value);
    $this->assertValidationErrors([
      '' => "The '$property' property cannot be changed.",
    ]);
  }

}
