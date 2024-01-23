<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Entity;

use Drupal\Core\Entity\Entity\EntityLinkSuggester;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;

/**
 * Tests validation of entity_link_suggester entities.
 *
 * @group Entity
 * @group Validation
 */
class EntityLinkSuggesterValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node'];

  /**
   * {@inheritdoc}
   */
  protected static array $propertiesWithOptionalValues = ['entity_types'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = EntityLinkSuggester::create([
      'admin_label' => 'Nodes only',
      'id' => 'nodes_only',
      'entity_types' => [
        'node' => [
          'entity_type' => 'node',
          'bundles' => NULL,
        ],
      ],
    ]);
    $this->entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function testImmutableProperties(array $valid_values = []): void {
    $valid_values['id'] = 'nodes_only_changed';
    parent::testImmutableProperties($valid_values);
  }

  /**
   * @testWith [{}, {"entity_types": "Allow suggestions for at least one entity type, or specify null to allow suggestions for all."}]
   *           [{"foo": {}}, {"entity_types.foo": ["'entity_type' is a required key.", "'bundles' is a required key."]}]
   *           [{"foo": {"entity_type": "node"}}, {"entity_types.foo": "'bundles' is a required key."}]
   *           [{"foo": {"entity_type": "node", "bundles": []}}, {"entity_types.foo.bundles": "This value should not be blank."}]
   *           [{"foo": {"bundles": null}}, {"entity_types.foo": "'entity_type' is a required key."}]
   *           [{"foo": {"entity_type": "bar", "bundles": null}}, {"entity_types.foo.entity_type": "The 'bar' plugin does not exist."}]
   *           [{"foo": {"entity_type": "node", "bundles": null}}, {}]
   *           [{"foo": {"entity_type": "node", "bundles": ["article"]}}, {}]
   */
  public function testEntityTypesValidation(?array $entity_types, array $expected_validation_errors): void {
    $this->entity->set('entity_types', $entity_types);
    $this->assertValidationErrors($expected_validation_errors);
  }

}
