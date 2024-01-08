<?php

namespace Drupal\KernelTests\Core\Entity;

use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;

/**
 * Tests validation of base_field_override entities.
 *
 * @group Entity
 * @group Validation
 */
class BaseFieldOverrideValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $fields = $this->container->get('entity_field.manager')
      ->getBaseFieldDefinitions('user');

    $this->entity = BaseFieldOverride::createFromBaseFieldDefinition($fields['uuid'], 'user');
    $this->entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function testImmutableProperties(array $valid_values = []): void {
    // If we don't clear the previous settings here, we will get unrelated
    // validation errors (in addition to the one we're expecting), because the
    // settings from the *old* field_type won't match the config schema for the
    // settings of the *new* field_type.
    $this->entity->set('settings', []);
    parent::testImmutableProperties(['field_type' => 'string']);
  }

  /**
   * Tests that the field type plugin's existence is validated.
   */
  public function testFieldTypePluginIsValidated(): void {
    // The `field_type` property is immutable, so we need to clone the entity in
    // order to cleanly change its field_type property to some invalid value.
    $this->entity = $this->entity->createDuplicate()
      ->set('field_type', 'invalid');
    $this->assertValidationErrors([
      'field_type' => "The 'invalid' plugin does not exist.",
    ]);
  }

}
