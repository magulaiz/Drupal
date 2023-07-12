<?php

namespace Drupal\Tests\field\Kernel\Entity;

use Drupal\entity_test\Entity\EntityTestMulBundle;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldStorageConfigInterface;

/**
 * Tests validation of field_config entities.
 *
 * @group field
 */
class FieldConfigValidationTest extends FieldStorageConfigValidationTest {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Specifically create a bundle for `entity_test_mul_with_bundle` content
    // entities confusingly named `entity_test`, to allow testing the modifying
    // of the `entity_type` field on FieldConfig entities without triggering
    // additional validation errors.
    // @see ::providerImmutableFields()
    EntityTestMulBundle::create([
      'id' => 'entity_test',
      'label' => $this->randomString(),
    ])->save();

    // Similar to the above, but now for testing the immutability of the bundle:
    // the bundle should exist to only get a validation error for immutability
    // violation.
    // @see ::providerImmutableFields()
    EntityTestMulBundle::create([
      'id' => 'foo',
      'label' => $this->randomString(),
    ])->save();

    // The field storage was created in the parent method.
    $field_storage = $this->entity;

    $this->entity = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'entity_test',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that validation fails if config dependencies are invalid.
   */
  public function testInvalidDependencies(): void {
    // Remove the config dependencies from the field entity.
    $dependencies = $this->entity->getDependencies();
    $dependencies['config'] = [];
    $this->entity->set('dependencies', $dependencies);

    $this->assertValidationErrors(['' => 'This field requires a field storage.']);

    // Things look sort-of like `field.storage.*.*` should fail validation
    // because they don't exist.
    $dependencies['config'] = [
      'field.storage.fake',
      'field.storage.',
      'field.storage.user.',
    ];
    $this->entity->set('dependencies', $dependencies);
    $this->assertValidationErrors([
      'dependencies.config.0' => "The 'field.storage.fake' config does not exist.",
      'dependencies.config.1' => "The 'field.storage.' config does not exist.",
      'dependencies.config.2' => "The 'field.storage.user.' config does not exist.",
    ]);
  }

  public function providerImmutableFields(): array {
    return [
      'field_name' => [
        ['field_name' => 'broken'],
      ],
      'entity_type' => [
        // @see ::setUp()
        ['entity_type' => 'entity_test'],
      ],
      'field_type' => [
        ['field_type' => 'email'],
      ],
      'bundle' => [
        // @see ::setUp()
        ['bundle' => 'foo'],
      ],
    ];
  }

  /**
   * Tests that the field type plugin is validated.
   */
  public function testFieldTypePlugin(): void {
    $this->entity->set('field_type', 'non_existent');
    $this->assertValidationErrors([
      '' => "The 'field_type' property cannot be changed.",
      'field_type' => "The 'non_existent' plugin does not exist.",
    ]);
  }

  /**
   * Tests that the target entity type is validated.
   *
   * 90% identical to the parent: an additional validation error is triggered
   * due to bundle validation.
   */
  public function testEntityType(): void {
    // Ensure the target entity type is valid to begin with.
    $this->assertValidationErrors([]);

    $this->entity->set('entity_type', 'strange_entity');
    $this->assertValidationErrors([
      '' => "The 'entity_type' property cannot be changed.",
      'entity_type' => "The 'strange_entity' plugin does not exist.",
      'bundle' => "The 'entity_test' bundle does not exist on the 'strange_entity' entity type.",
    ]);

    // A valid, but non-fieldable, entity type should raise an error.
    $this->entity->set('entity_type', 'field_config');
    $this->assertValidationErrors([
      '' => "The 'entity_type' property cannot be changed.",
      'entity_type' => "The 'field_config' plugin must implement or extend \Drupal\Core\Entity\FieldableEntityInterface.",
      'bundle' => "The 'entity_test' bundle does not exist on the 'field_config' entity type.",
    ]);
  }

  /**
   * Tests that the bundle is validated.
   */
  public function testBundle(): void {
    $entity_type_id = 'entity_test_mul_with_bundle';
    $field_name = 'test';

    // Assert that the FieldStorageConfig which this FieldConfig will depend on,
    // already exists.
    $field_storage_config = FieldStorageConfig::loadByName($entity_type_id, $field_name);
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storage_config);

    // Try to create an instance of this field on a bundle that does not exist.
    $this->entity = FieldConfig::create([
      'entity_type' => $entity_type_id,
      'field_name' => $field_name,
      'field_type' => $field_storage_config->getType(),
      'bundle' => 'non_existent',
    ]);
    $this->assertValidationErrors([
      'bundle' => "The 'non_existent' bundle does not exist on the 'entity_test_mul_with_bundle' entity type.",
    ]);

    // Next, try to create it on a bundle that does exist.
    $this->entity->set('bundle', 'entity_test');
    $this->assertValidationErrors([]);
  }

}
