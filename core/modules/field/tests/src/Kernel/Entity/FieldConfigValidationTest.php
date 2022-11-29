<?php

namespace Drupal\Tests\field\Kernel\Entity;

use Drupal\field\Entity\FieldConfig;

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

    // The field storage was created in the parent method.
    $field_storage = $this->entity;

    $this->entity = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'user',
    ]);
    $this->entity->save();
  }

  public function testNoFieldStorageInDependencies(): void {
    $storage_id = $this->entity->getFieldStorageDefinition()
      ->getConfigDependencyName();

    $dependencies = $this->entity->getDependencies();
    $dependencies['config'] = array_values(array_diff($dependencies['config'], [$storage_id]));
    $this->entity->set('dependencies', $dependencies);

    $violations = $this->validateEntity();
    $this->assertCount(1, $violations);
  }

}
