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
   * {@inheritdoc}
   */
  public function testImmutableFields(array $valid_values = []): void {
    // Clear all settings so that, when the immutable `field_type` property is
    // changed by the parent method, we don't get errors about unsupported
    // settings.
    $this->entity->set('settings', []);
    parent::testImmutableFields($valid_values);
  }

}
