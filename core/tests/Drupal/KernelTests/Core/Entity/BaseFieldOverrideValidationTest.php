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
   * Tests that the field type plugin's existence is validated.
   */
  public function testFieldTypePluginIsValidated(): void {
    $this->entity->set('field_type', 'invalid');
    $this->assertValidationErrors([
      'field_type' => "The 'invalid' plugin does not exist.",
    ]);
  }

}
