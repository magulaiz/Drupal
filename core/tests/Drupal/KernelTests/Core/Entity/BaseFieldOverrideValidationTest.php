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

    $this->entity = BaseFieldOverride::createFromBaseFieldDefinition(reset($fields), 'user');
    $this->entity->save();
  }

  /**
   * Tests that the bundle is validated.
   */
  public function testBundle(): void {
    $fields = $this->container->get('entity_field.manager')
      ->getBaseFieldDefinitions('user');

    // Try to create an instance of this base field override on a bundle that
    // does not exist.
    $this->entity = BaseFieldOverride::createFromBaseFieldDefinition(reset($fields), 'non_existent');
    $this->assertValidationErrors([
      'bundle' => "The 'non_existent' bundle does not exist on the 'user' entity type.",
    ]);

    // Next, try to create it on a bundle that does exist.
    $this->entity = BaseFieldOverride::createFromBaseFieldDefinition(reset($fields), 'user');
    $this->assertValidationErrors([]);
  }

}
