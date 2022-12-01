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
  protected static $modules = ['field', 'entity_test'];

  /**
   * Tests that immutable fields cannot be changed.
   */
  public function testImmutableFields(): void {
    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'test',
      'entity_type' => 'entity_test',
      'type' => 'boolean',
    ]);
    $field_storage->save();

    $field_storage->set('field_name', 'broken');
    $field_storage->set('entity_type', 'entity_test_mul');
    $field_storage->set('type', 'email');
    $field_storage->set('module', 'entity_test');
    $field_storage->set('custom_storage', !$field_storage->hasCustomStorage());

    $typed_data = $this->container->get('typed_data_manager');
    $definition = $typed_data->createDataDefinition('entity:field_storage_config');
    $violations = $typed_data->create($definition, $field_storage)->validate();
    $this->assertCount(5, $violations);

    $this->assertSame("The 'field_name' property cannot be changed.", (string) $violations->get(0)->getMessage());
    $this->assertSame("The 'entity_type' property cannot be changed.", (string) $violations->get(1)->getMessage());
    $this->assertSame("The 'type' property cannot be changed.", (string) $violations->get(2)->getMessage());
    $this->assertSame("The 'module' property cannot be changed.", (string) $violations->get(3)->getMessage());
    $this->assertSame("The 'custom_storage' property cannot be changed.", (string) $violations->get(4)->getMessage());
  }

}
