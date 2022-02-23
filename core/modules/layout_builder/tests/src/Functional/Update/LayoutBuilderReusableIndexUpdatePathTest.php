<?php

namespace Drupal\Tests\layout_builder\Functional\Update;

use Drupal\Core\Database\Database;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the upgrade path for Layout Builder reusable column index.
 *
 * @group layout_builder
 */
class LayoutBuilderReusableIndexUpdatePathTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.0.0.bare.standard.php.gz',
    ];
  }

  /**
   * Tests the upgrade path for Layout Builder layout context mappings.
   */
  public function testRunUpdates() {
    $connection = Database::getConnection();

    $this->assertFalse($connection->schema()->indexExists('block_content_field_data', 'block_content__reusable'), 'Block Content reusable index not yet added.');

    $this->runUpdates();

    $this->assertTrue($connection->schema()->indexExists('block_content_field_data', 'block_content__reusable'), 'Block Content reusable index has been added.');
  }
}
