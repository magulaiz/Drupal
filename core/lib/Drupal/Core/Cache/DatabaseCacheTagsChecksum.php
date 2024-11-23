<?php

namespace Drupal\Core\Cache;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\LazyTableCreationTrait;

/**
 * Cache tags invalidations checksum implementation that uses the database.
 */
class DatabaseCacheTagsChecksum implements CacheTagsChecksumInterface, CacheTagsInvalidatorInterface {

  use CacheTagsChecksumTrait;
  use LazyTableCreationTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The database table name.
   *
   * @var string
   */
  protected readonly string $table = 'cachetags';

  /**
   * Constructs a DatabaseCacheTagsChecksum object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  protected function doInvalidateTags(array $tags) {
    try {
      foreach ($tags as $tag) {
        $this->connection->merge($this->table)
          ->insertFields(['invalidations' => 1])
          ->expression('invalidations', '[invalidations] + 1')
          ->key('tag', $tag)
          ->execute();
      }
    }
    catch (\Exception $e) {
      // Create the cache table, which will be empty. This fixes cases during
      // core install where cache tags are invalidated before the table is
      // created.
      if (!$this->ensureTableExists()) {
        throw $e;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getTagInvalidationCounts(array $tags) {
    try {
      return $this->connection->query('SELECT [tag], [invalidations] FROM {' . $this->table . '} WHERE [tag] IN ( :tags[] )', [':tags[]' => $tags])
        ->fetchAllKeyed();
    }
    catch (\Exception $e) {
      // If the table does not exist yet, create.
      if (!$this->ensureTableExists()) {
        throw $e;
      }
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function schemaDefinition() {
    return [
      'description' => 'Cache table for tracking cache tag invalidations.',
      'fields' => [
        'tag' => [
          'description' => 'Namespace-prefixed tag string.',
          'type' => 'varchar_ascii',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ],
        'invalidations' => [
          'description' => 'Number incremented when the tag is invalidated.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ],
      ],
      'primary key' => ['tag'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDatabaseConnection() {
    return $this->connection;
  }

}
