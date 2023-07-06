<?php

declare(strict_types=1);

namespace Drupal\Tests\forum\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests addition of the forum_index primary key.
 *
 * @group forum
 */
final class ForumIndexUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      dirname(__DIR__, 2) . '/fixtures/update/drupal-10.1.0.empty.testing.forum.gz',
    ];
  }

  /**
   * Tests the update path to add the new primary key.
   */
  public function testUpdatePath(): void {
    $schema = \Drupal::database()->schema();
    // We can't reliably call ::indexExists for each database driver as sqlite
    // doesn't have named indexes for primary keys like mysql (PRIMARY) and
    // pgsql (pkey).
    $find_primary_key_columns = new \ReflectionMethod(get_class($schema), 'findPrimaryKeyColumns');
    $find_primary_key_columns->setAccessible(TRUE);
    $columns = $find_primary_key_columns->invoke($schema, 'forum_index');
    $this->assertEmpty($columns);
    $this->runUpdates();
    $this->assertEquals(['nid', 'tid'], $find_primary_key_columns->invoke($schema, 'forum_index'));
  }

}
