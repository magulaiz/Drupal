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

  /**
   * Tests that validation fails if field storage is not in the dependencies.
   */
  public function testNoFieldStorageInDependencies(): void {
    // Remove the field storage from the config dependencies, using
    // array_splice() to force a re-key.
    $dependencies = $this->entity->getDependencies();
    $index = array_search('field.storage.user.test', $dependencies['config']);
    $this->assertIsInt($index);
    array_splice($dependencies['config'], $index, 1);
    $this->entity->set('dependencies', $dependencies);

    $violations = $this->validateEntity();
    $this->assertCount(1, $violations);
    $this->assertSame('Does not contain a value matching "/^field\.storage\.\w+\.\w+$/".', (string) $violations->get(0)->getMessage());
  }

}
