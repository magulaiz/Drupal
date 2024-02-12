<?php

namespace Drupal\mongodb\Driver\Database\mongodb\Cache;

use Drupal\Core\Cache\DatabaseCacheTagsChecksum as CoreDatabaseCacheTagsChecksum;
use Drupal\mongodb\Driver\Database\mongodb\Statement;

/**
 * The MongoDB implementation of \Drupal\Core\Cache\DatabaseCacheTagsChecksum.
 */
class DatabaseCacheTagsChecksum extends CoreDatabaseCacheTagsChecksum {

  /**
   * Indicator for the existence of the database table.
   *
   * @var bool
   */
  protected $tableExists = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function calculateChecksum(array $tags) {
    $checksum = 0;

    $query_tags = array_diff($tags, array_keys($this->tagCache));
    if ($query_tags) {
      $db_tags = [];
      try {
        $prefixed_table = $this->connection->getMongodbPrefixedTable('cachetags');
        $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
          ['tag' => ['$in' => array_values($query_tags)]],
          ['projection' => ['tag' => 1, 'invalidations' => 1, 'created' => 1, 'expire' => 1, 'serialized' => 1, 'tags' => 1, 'checksum' => 1, '_id' => 0]]
        );

        $statement = new Statement($this->connection, $cursor, ['tag', 'invalidations']);
        $db_tags = $statement->execute()->fetchAllKeyed();
        $this->tagCache += $db_tags;
      }
      catch (\Exception $e) {
        // If the table does not exist yet, create.
        if (!$this->ensureTableExists()) {
          $this->catchException($e);
        }
      }
      // Fill static cache with empty objects for tags not found in the database.
      $this->tagCache += array_fill_keys(array_diff($query_tags, array_keys($db_tags)), 0);
    }

    foreach ($tags as $tag) {
      $checksum += $this->tagCache[$tag];
    }

    return (string) $checksum;
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateTags(array $tags) {
    // For MongoDB the table need to exists. Otherwise MongoDB creates one
    // without the correct validation.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    parent::invalidateTags($tags);
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentChecksum(array $tags) {
    // For MongoDB the table need to exists. Otherwise MongoDB creates one
    // without the correct validation.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    // Make sure that this method returns a string value.
    return (string) parent::getCurrentChecksum($tags);
  }

}
