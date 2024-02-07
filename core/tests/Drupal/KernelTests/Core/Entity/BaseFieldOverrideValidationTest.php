<?php

namespace Drupal\KernelTests\Core\Entity;

use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\entity_test\Entity\EntityTestBundle;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * Tests validation of base_field_override entities.
 *
 * @group Entity
 * @group Validation
 */
class BaseFieldOverrideValidationTest extends ConfigEntityValidationTestBase {

  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static array $propertiesWithOptionalValues = [
    'default_value',
    'description',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test', 'field', 'node', 'text', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig('node');
    $this->createContentType(['type' => 'one']);
    $this->createContentType(['type' => 'another']);

    EntityTestBundle::create(['id' => 'one'])->save();
    EntityTestBundle::create(['id' => 'another'])->save();

    $fields = $this->container->get('entity_field.manager')
      ->getBaseFieldDefinitions('node');

    $this->entity = BaseFieldOverride::createFromBaseFieldDefinition(reset($fields), 'one');
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
    $this->entity = BaseFieldOverride::createFromBaseFieldDefinition($fields['uuid'], 'non_existent');
    $this->assertValidationErrors([
      'bundle' => "The 'non_existent' bundle does not exist on the 'user' entity type.",
    ]);

    // Next, try to create it on a bundle that does exist.
    $this->entity = BaseFieldOverride::createFromBaseFieldDefinition($fields['uuid'], 'user');
    $this->assertValidationErrors([]);
  }

  /**
   * Tests that the target bundle of the field is checked.
   */
  public function testTargetBundleMustExist(): void {
    $this->entity->set('bundle', 'nope');
    $this->assertValidationErrors([
      '' => "The 'bundle' property cannot be changed.",
      'bundle' => "The 'nope' bundle does not exist on the 'node' entity type.",
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function testImmutableProperties(array $valid_values = [], ?array $additional_expected_validation_errors_when_modified = NULL): void {
    // If we don't clear the previous settings here, we will get unrelated
    // validation errors (in addition to the one we're expecting), because the
    // settings from the *old* field_type won't match the config schema for the
    // settings of the *new* field_type.
    $this->entity->set('settings', []);
    parent::testImmutableProperties([
      'entity_type' => 'entity_test_with_bundle',
      'bundle' => 'another',
      'field_type' => 'email',
    ]);
  }

}
