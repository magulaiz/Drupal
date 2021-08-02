<?php

namespace Drupal\Tests\migrate\Kernel;

use Drupal\Core\Serialization\Yaml;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;

/**
 * Tests the destination Entity plugin.
 *
 * @group migrate
 */
class MigrateEntityDestinationTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field',
    'migrate_destination_test',
    'node',
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    NodeType::create([
      'type' => 'test_node_type_no_fields',
      'name' => 'Test node type without fields',
    ])->save();

    NodeType::create([
      'type' => 'test_node_type_with_fields',
      'name' => 'Test node type with fields',
    ])->save();
  }

  /**
   * Test destination fields() method.
   */
  public function testDestinationField() {
    // Test with a migration with a default bundle that does not have fields.
    $node_no_fields_definition = Yaml::decode(
      <<<EOT
      id: node_no_fields
      label: Migrate to no bundle specified destination
      source:
        plugin: migrate_destination_test
        constants:
          type: test_node_type_no_fields
      process:
        type: constants/type
        title: title
      destination:
        plugin: entity:node
        default_bundle: test_node_type_no_fields
      EOT
    );

    $node_no_fields_migration = \Drupal::service('plugin.manager.migration')->createStubMigration($node_no_fields_definition);
    $node_no_fields_destination = $node_no_fields_migration->getDestinationPlugin();
    $this->assertArrayHasKey('nid', $node_no_fields_destination->fields());
    $this->assertArrayNotHasKey('field_text', $node_no_fields_destination->fields());

    // Test with a migration with a default bundle that has fields.
    $node_with_fields_definition = Yaml::decode(
      <<<EOT
      id: node_with_fields
      label: Migrate to bundle specified destination
      source:
        plugin: migrate_destination_test
        constants:
          type: test_node_type_with_fields
      process:
        type: constants/type
        title: title
      destination:
        plugin: entity:node
        default_bundle: test_node_type_with_fields
      EOT
    );

    $node_with_fields_migration = \Drupal::service('plugin.manager.migration')->createStubMigration($node_with_fields_definition);
    $node_with_fields_destination = $node_with_fields_migration->getDestinationPlugin();

    $this->assertArrayHasKey('nid', $node_with_fields_destination->fields());
    $this->assertArrayNotHasKey('field_text', $node_with_fields_destination->fields());

    // Create a text field attached to 'test_node_type_with_fields' node type.
    FieldStorageConfig::create([
      'type' => 'string',
      'entity_type' => 'node',
      'field_name' => 'field_text',
    ])->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => 'test_node_type_with_fields',
      'field_name' => 'field_text',
    ])->save();

    $this->assertArrayHasKey('field_text', $node_with_fields_destination->fields());
    // The destination_bundle_entity migration has default bundle of
    // test_node_type so it shouldn't show the fields on other node types.
    $this->assertArrayNotHasKey('field_text', $node_no_fields_destination->fields());

    // Test with a user entity
    $user_with_fields_definition = Yaml::decode(
      <<<EOT
      id: user_with_fields
      label: Migrate to bundle specified destination
      source:
        plugin: migrate_destination_test
        constants:
          type: test_user_with_fields
      process:
        type: constants/type
        title: title
      destination:
        plugin: entity:user
      EOT
    );

    $user_with_fields_migration = \Drupal::service('plugin.manager.migration')->createStubMigration($user_with_fields_definition);
    $user_with_fields_destination = $user_with_fields_migration->getDestinationPlugin();

    $this->assertArrayHasKey('uid', $user_with_fields_destination->fields());
    $this->assertArrayNotHasKey('field_text', $user_with_fields_destination->fields());

    // Create a text field attached to the user entity.
    FieldStorageConfig::create([
      'type' => 'string',
      'entity_type' => 'user',
      'field_name' => 'field_text',
    ])->save();

    FieldConfig::create([
      'entity_type' => 'user',
      'bundle' => 'user',
      'field_name' => 'field_text',
    ])->save();

    $this->assertArrayHasKey('field_text', $user_with_fields_destination->fields());
  }

}
