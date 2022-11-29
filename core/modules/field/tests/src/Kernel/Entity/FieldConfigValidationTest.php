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

    $violations = $this->validateEntity();
    $this->assertCount(2, $violations);
    $this->assertSame('This collection should contain <em class="placeholder">1</em> element or more.', (string) $violations->get(0)->getMessage());
    $this->assertSame('Does not contain a value matching "/^field\.storage\.\w+\.\w+$/".', (string) $violations->get(1)->getMessage());

    // Things look sort-of like `field.storage.*.*` should fail validation.
    $dependencies['config'] = [
      'field.storage.fake',
      'field.storage.',
      'field.storage.user.',
    ];
    $this->entity->set('dependencies', $dependencies);
    $violations = $this->validateEntity();
    $this->assertCount(4, $violations);
    $this->assertSame('Does not contain a value matching "/^field\.storage\.\w+\.\w+$/".', (string) $violations->get(0)->getMessage());
    // Each of the items in the config dependencies list should be flagged as
    // non-existent config.
    for ($i = 0; $i < 3; $i++) {
      $message = sprintf("The '%s' config does not exist.", $dependencies['config'][$i]);
      $this->assertSame($message, (string) $violations->get($i + 1)->getMessage());
    }
  }

}
