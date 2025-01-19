<?php

declare(strict_types=1);

namespace Drupal\Tests\taxonomy\Kernel\Migrate;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate_drupal\Tests\StubTestTrait;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\TermInterface;
use Drupal\Tests\migrate_drupal\Kernel\MigrateDrupalTestBase;

/**
 * Test stub creation for taxonomy terms.
 *
 * @group taxonomy
 */
class MigrateTaxonomyTermStubTest extends MigrateDrupalTestBase {

  use StubTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['taxonomy', 'text', 'taxonomy_term_stub_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('taxonomy_term');
  }

  /**
   * Tests creation of taxonomy term stubs.
   */
  public function testStub(): void {
    Vocabulary::create([
      'vid' => 'test_vocabulary',
      'name' => 'Test vocabulary',
    ])->save();
    $this->performStubTest('taxonomy_term');
  }

  /**
   * Tests creation of stubs when parent is stubbed.
   */
  public function testStubWithParentStub(): void {
    // Create a vocabulary via migration for the terms to reference.
    $vocabulary_data_rows = [
      ['id' => '1', 'name' => 'tags'],
    ];
    $ids = ['id' => ['type' => 'integer']];
    $definition = [
      'migration_tags' => ['Stub test'],
      'source' => [
        'plugin' => 'embedded_data',
        'data_rows' => $vocabulary_data_rows,
        'ids' => $ids,
      ],
      'process' => [
        'vid' => 'id',
        'name' => 'name',
      ],
      'destination' => ['plugin' => 'entity:taxonomy_vocabulary'],
    ];
    $vocabulary_migration = \Drupal::service('plugin.manager.migration')->createStubMigration($definition);
    $vocabulary_executable = new MigrateExecutable($vocabulary_migration, $this);
    $vocabulary_executable->import();

    // The "taxonomy_term_stub_test_valid" migration references a valid (but not
    // yet migrated) term parent. Here we ensure that a valid stub is created
    // for the parent of the migrated child taxonomy term.
    $migration_valid = $this->getMigration('taxonomy_term_stub_test_valid');
    $migration_missing_stub = $this->getMigration('taxonomy_term_stub_test');
    $term_executable = new MigrateExecutable($migration_valid, $this);
    $term_executable->import();
    $this->assertNotEmpty($stub_row_3 = $migration_missing_stub->getIdMap()->getRowBySource(['3']), 'Stub row exists in the ID map table');
    // Load the referenced term, which should exist as a stub.
    $stub_entity = Term::load(3);
    $this->assertEquals(MigrateIdMapInterface::STATUS_NEEDS_UPDATE, $stub_row_3['source_row_status']);
    $this->assertTrue($stub_entity instanceof TermInterface, 'Stub successfully created');
    $this->assertCount(0, $stub_entity->validate(), 'Stub is a valid entity');

    // The "taxonomy_term_stub_test" migration's first row (with ID 1)
    // references an invalid (not migrated and missing) term parent: let's
    // ensure that no stub is created.
    $term_executable = new MigrateExecutable($migration_missing_stub, $this);
    $term_executable->import();
    // The previously stubbed parent term should be fully migrated.
    $this->assertEquals(MigrateIdMapInterface::STATUS_IMPORTED, $migration_missing_stub->getIdMap()->getRowBySource(['3'])['source_row_status']);
    $this->assertEquals('cat', Term::load(3)->label());
    $this->assertEmpty($migration_missing_stub->getIdMap()->getRowBySource(['2']), 'Stub row does not exist in the ID map table, since "2" is missing from the source rows');
  }

}
