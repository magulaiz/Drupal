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

  /**
   * Tests that immutable fields cannot be changed.
   *
   * @param array $fields_to_change
   *   An array of key-value pairs with field names as keys and the value to set
   *   on the field as values.
   *
   * @dataProvider providerImmutableFields
   */
  public function testImmutableFields(array $fields_to_change): void {
    $expected_messages = [];
    foreach ($fields_to_change as $field_name => $new_value) {
      $this->entity->set($field_name, $new_value);
      $expected_messages[] = "The '$field_name' property cannot be changed.";
    }
    $this->assertValidationErrors($expected_messages);
  }

  /**
   * Tests that the field type plugin is validated.
   */
  public function testFieldTypePlugin(): void {
    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'test',
      'entity_type' => 'entity_test',
      'type' => 'non_existent',
      'module' => 'core',
    ]);

    $typed_data = $this->container->get('typed_data_manager');
    $definition = $typed_data->createDataDefinition('entity:field_storage_config');
    $violations = $typed_data->create($definition, $field_storage)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("The 'non_existent' plugin does not exist.", (string) $violations->get(0)->getMessage());
  }

  /**
   * Tests that the target entity type is validated.
   */
  public function testEntityType(): void {
    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'test',
      'entity_type' => 'entity_test',
      'type' => 'boolean',
      'module' => 'core',
    ]);

    $typed_data = $this->container->get('typed_data_manager');
    $definition = $typed_data->createDataDefinition('entity:field_storage_config');
    $violations = $typed_data->create($definition, $field_storage)->validate();
    $this->assertCount(0, $violations);

    $field_storage->set('entity_type', 'strange_entity');
    $violations = $typed_data->create($definition, $field_storage)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("The 'strange_entity' plugin does not exist.", (string) $violations->get(0)->getMessage());

    $field_storage->set('entity_type', 'field_config');
    $violations = $typed_data->create($definition, $field_storage)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("The 'field_config' plugin must implement or extend \Drupal\Core\Entity\FieldableEntityInterface.", (string) $violations->get(0)->getMessage());
  }

}
