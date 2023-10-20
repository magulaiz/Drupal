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
  public function testImmutableProperties(array $valid_values = [], array $indirect_consequences = []): void {
    parent::testImmutableProperties($valid_values, [
      'field_type' => [
        'settings' => [
          "'min' is an unknown key because field_type is <RANDOM> (see config schema type field.field_settings.*).",
          "'max' is an unknown key because field_type is <RANDOM> (see config schema type field.field_settings.*).",
          "'prefix' is an unknown key because field_type is <RANDOM> (see config schema type field.field_settings.*).",
          "'suffix' is an unknown key because field_type is <RANDOM> (see config schema type field.field_settings.*).",
        ],
      ],
    ]);
  }

}
