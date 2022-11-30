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
   * Tests that validation fails if config dependencies are invalid.
   */
  public function testInvalidDependencies(): void {
    // Remove the config dependencies from the field entity.
    $dependencies = $this->entity->getDependencies();
    $dependencies['config'] = [];
    $this->entity->set('dependencies', $dependencies);

    $this->assertValidationErrors([
      'This collection should contain <em class="placeholder">1</em> element or more.',
      'Does not contain a value matching "/^field\.storage\.\w+\.\w+$/".',
    ]);

    // Things look sort-of like `field.storage.*.*` should fail validation.
    $dependencies['config'] = [
      'field.storage.fake',
      'field.storage.',
      'field.storage.user.',
    ];
    $this->entity->set('dependencies', $dependencies);
    $this->assertValidationErrors([
      'Does not contain a value matching "/^field\.storage\.\w+\.\w+$/".',
      // Each of the items in the config dependencies list should be flagged as
      // non-existent config.
      "The 'field.storage.fake' config does not exist.",
      "The 'field.storage.' config does not exist.",
      "The 'field.storage.user.' config does not exist.",
    ]);
  }

}
