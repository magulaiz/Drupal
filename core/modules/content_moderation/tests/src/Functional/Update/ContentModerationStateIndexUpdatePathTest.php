<?php

namespace Drupal\Tests\content_moderation\Functional\Update;

use Drupal\Core\Database\Database;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the upgrade path for adding an index to moderation state column.
 *
 * @group content_moderation
 */
class ContentModerationStateIndexUpdatePathTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.0.0.filled.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/drupal-9.0.0.content-moderation.php',
    ];
  }

  /**
   * Tests the upgrade path for moderation state reindexing.
   */
  public function testRunUpdates() {
    $table = 'content_moderation_state_field_revision';
    $name = 'content_moderation_state_field__moderation_state';

    $connection = Database::getConnection();

    $this->assertFalse($connection->schema()->indexExists($table, $name));

    $this->runUpdates();

    $this->assertTrue($connection->schema()->indexExists($table, $name));
  }

}
