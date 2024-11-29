<?php

namespace Drupal\Core\Cache;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseException;

/**
 * Cache tags invalidations checksum implementation that uses the database.
 */
class DatabaseCacheTagsChecksum implements CacheTagsChecksumInterface, CacheTagsInvalidatorInterface {

  use CacheTagsChecksumTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

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
    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function () use ($tags): void {
        foreach ($tags as $tag) {
          $this->connection->merge('cachetags')
            ->insertFields(['invalidations' => 1])
            ->expression('invalidations', '[invalidations] + 1')
            ->key('tag', $tag)
            ->execute();
        }
      },
      schema: [
        'cachetags' => $this->schemaDefinition(),
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getTagInvalidationCounts(array $tags) {
    $return = $this->connection->executeEnsuringSchemaOnFailure(
      execute: function () use ($tags): array {
        return $this->connection->query('SELECT [tag], [invalidations] FROM {cachetags} WHERE [tag] IN ( :tags[] )', [':tags[]' => $tags])
          ->fetchAllKeyed();
      },
      schema: [
        'cachetags' => $this->schemaDefinition(),
      ],
    );
    return $return === FALSE ? [] : $return;
  }

  /**
   * Defines the schema for the {cachetags} table.
   *
   * @internal
   */
  public function schemaDefinition() {
    $schema = [
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
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getDatabaseConnection() {
    return $this->connection;
  }

}
