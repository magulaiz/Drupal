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
    $count = \Drupal::database()->select('forum_index')->countQuery()->execute()->fetchField();
    $this->assertEquals(6, $count);
    $duplicates = \Drupal::database()->select('forum_index')->condition('nid', 1)->countQuery()->execute()->fetchField();
    $this->assertEquals(2, $duplicates);
    $this->runUpdates();
    $this->assertEquals(['nid', 'tid'], $find_primary_key_columns->invoke($schema, 'forum_index'));
    $count = \Drupal::database()->select('forum_index')->countQuery()->execute()->fetchField();
    $this->assertEquals(5, $count);
    $duplicates = \Drupal::database()->select('forum_index')->condition('nid', 1)->countQuery()->execute()->fetchField();
    $this->assertEquals(1, $duplicates);
    $entry = \Drupal::database()->select('forum_index', 'f')->fields('f')->condition('nid', 5)->execute()->fetchAssoc();
    $this->assertEquals([
      'nid' => 5,
      'title' => 'AFL',
      'tid' => 5,
      'sticky' => 0,
      'created' => 1695264369,
      'last_comment_timestamp' => 1695264403,
      'comment_count' => 1,
    ], $entry);
  }

}
