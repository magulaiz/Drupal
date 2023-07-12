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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = FieldStorageConfig::create([
      'type' => 'boolean',
      'field_name' => 'test',
      'entity_type' => 'entity_test_mul_with_bundle',
      'custom_storage' => FALSE,
    ]);
    $this->entity->save();
  }

  public function providerImmutableFields(): array {
    return [
      'field_name' => [
        ['field_name' => 'broken'],
      ],
      'entity_type' => [
        ['entity_type' => 'entity_test'],
      ],
      'type' => [
        ['type' => 'email'],
      ],
      'module' => [
        ['module' => 'entity_test'],
      ],
      'custom_storage' => [
        ['custom_storage' => TRUE],
      ],
    ];
  }

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
      $expected_messages[''] = "The '$field_name' property cannot be changed.";
    }
    $this->assertValidationErrors($expected_messages);
  }

  /**
   * Tests that the field type plugin is validated.
   */
  public function testFieldTypePlugin(): void {
    $this->entity->set('type', 'non_existent');
    $this->assertValidationErrors([
      '' => "The 'type' property cannot be changed.",
      'type' => "The 'non_existent' plugin does not exist.",
    ]);
  }

  /**
   * Tests that the target entity type is validated.
   */
  public function testEntityType(): void {
    // Ensure the target entity type is valid to begin with.
    $this->assertValidationErrors([]);

    $this->entity->set('entity_type', 'strange_entity');
    $this->assertValidationErrors([
      '' => "The 'entity_type' property cannot be changed.",
      'entity_type' => "The 'strange_entity' plugin does not exist.",
    ]);

    // A valid, but non-fieldable, entity type should raise an error.
    $this->entity->set('entity_type', 'field_config');
    $this->assertValidationErrors([
      '' => "The 'entity_type' property cannot be changed.",
      'entity_type' => "The 'field_config' plugin must implement or extend \Drupal\Core\Entity\FieldableEntityInterface.",
    ]);
  }

}
