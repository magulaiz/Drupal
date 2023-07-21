<?php

namespace Drupal\Tests\system\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\Tests\UpdatePathTestTrait;

/**
 * Tests update of schema for timestamp fields to bigint.
 *
 * @group system
 */
class Y2038SchemaUpdateTest extends UpdatePathTestBase {

  use UpdatePathTestTrait;

  /**
   * A user with some relevant administrative permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The entities and time fields.
   *
   * @var string[][]
   */
  protected $timestampFields = [
    ["block_content", "changed"],
    ["comment", "changed"],
    ["file", "changed"],
    ["node", "changed"],
    ["taxonomy_term", "changed"],
    ["user", "changed"],
    ["menu_link_content", "changed"],
    ["user", "access"],
    ["user", "login"],
    ["block_content", "revision_created"],
    ["comment", "created"],
    ["file", "created"],
    ["node", "revision_timestamp"],
    ["node", "created"],
    ["taxonomy_term", "revision_created"],
    ["taxonomy_term", "content_translation_created"],
    ["user", "created"],
    ["user", "content_translation_created"],
    ["menu_link_content", "revision_created"],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      // Start with a filled standard install of Drupal 9.4.0.
      DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-9.4.0.filled.standard.php.gz',
    ];
  }

  /**
   * Tests update of time fields.
   */
  public function testUpdate() {
    if (\Drupal::service('database')->databaseType() == 'sqlite') {
      $this->markTestSkipped("This test does not support the SQLite database driver.");
    }

    $this->assertBeforeSpecification(['int', 'integer', 'bigint']);

    $this->runUpdates();

    $this->assertAfterSpecifications(['int']);
  }

  /**
   * Asserts the field storage specifications before the update.
   */
  public function assertBeforeSpecification($expected_values): void {
    foreach ($this->timestampFields as $field_data) {
      [
        $specification_original,
        $specification_current,
      ] = $this->getSpecifications($field_data);

      // The original specification is for a small int.
      $this->assertArrayNotHasKey('size', $specification_original, "Failed for '$field_data[0]' original specification '$field_data[1]'");
      $this->assertContains($specification_original['type'], $expected_values, "Failed for '$field_data[0]' original specification '$field_data[1]'");

      // The current specification is for a size of 'big'.
      $this->assertArrayHasKey('size', $specification_current, "Failed for '$field_data[0]' original specification '$field_data[1]'");
      $this->assertEquals('big', $specification_current['size'], "Failed for '$field_data[0]' original specification '$field_data[1]'");
      $this->assertContains($specification_current['type'], $expected_values, "Failed for '$field_data[0]' original specification '$field_data[1]'");
    }
  }

  /**
   * Asserts the field storage specifications after the update.
   */
  public function assertAfterSpecifications($expected_values): void {
    // Log in to access the log messages.
    $this->adminUser = $this->drupalCreateUser([
      'access site reports',
    ]);
    $this->drupalLogin($this->adminUser);
    $event = 15;
    foreach ($this->timestampFields as $field_data) {
      [
        $specification_original,
        $specification_current,
      ] = $this->getSpecifications($field_data);

      // The original is updated to size of 'big'.
      $this->assertEquals($specification_original['size'], 'big', "Failed for '$field_data[0]' original specification '$field_data[1]'");
      $this->assertContains($specification_original['type'], $expected_values, "Failed for '$field_data[0]' original specification '$field_data[1]'");

      // The current specification is still a big integer.
      $this->assertEquals($specification_current['size'], 'big', "Failed for '$field_data[0]' original specification '$field_data[1]'");
      $this->assertContains($specification_current['type'], $expected_values, "Failed for '$field_data[0]' original specification '$field_data[1]'");

      // Check the log output for the expected success message.
      $this->drupalGet("admin/reports/dblog/event/$event");
      $this->assertSession()
        ->pageTextContainsOnce("Successfully updated entity '$field_data[0]' field '$field_data[1]' to remove year 2038 limitation.");
      $event++;
    }
  }

  /**
   * Gets the specifications for the provided fields.
   *
   * @param array $field_data
   *   An array with two values, the entity type ID and the field name.
   *
   * @return array
   *   An indexed array containing the original specification and the current
   *   specification.
   */
  public function getSpecifications(array $field_data): array {
    [$field_data[0], $field_data[1]] = $field_data;
    $last_installed_schema_repository = \Drupal::service('entity.last_installed_schema.repository');

    // Get the original storage definition for this field.
    $original_storage_definitions = $last_installed_schema_repository->getLastInstalledFieldStorageDefinitions($field_data[0]);
    $field_schema_original = $original_storage_definitions[$field_data[1]]->getSchema();
    $specification_original = $field_schema_original['columns']['value'];

    // Get the current storage definition for this field.
    $storage_definitions = \Drupal::service('entity_field.manager')
      ->getFieldStorageDefinitions($field_data[0]);
    $storage_definition = $storage_definitions[$field_data[1]];
    $field_schema_current = $storage_definition->getSchema();
    $specification_current = $field_schema_current['columns']['value'];
    return [
      $specification_original,
      $specification_current,
    ];
  }

}
