<?php

namespace Drupal\Core\Database;

/**
 * Provides methods for the lazy table creation in services.
 */
trait LazyTableCreationTrait {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The table name.
   *
   * @var string
   */
  protected $table;

  /**
   * Defines the schema for the table.
   *
   * @return array
   *   Return the table creation schema.
   *
   * @internal
   *   The method is made public to not cause a BC break when replacing existing
   *   implementations.
   */
  abstract public function schemaDefinition();

  /**
   * Act on an exception when the table might not be created.
   *
   * If the table does not yet exist, that's fine, but if the table exists and
   * yet the query failed, then the table is stale and the exception needs to
   * propagate.
   *
   * @param \Exception $e
   *   The exception.
   *
   * @throws \RuntimeException
   *   Thrown when the variable $this->table is not set.
   */
  protected function catchException(\Exception $e) {
    if (empty($this->table)) {
      throw new \RuntimeException('The variable $this->table is not set.');
    }

    if ($this->connection->schema()->tableExists($this->table)) {
      throw $e;
    }
  }

  /**
   * Check if that the table exists and create it when it is not.
   *
   * @return bool
   *   TRUE if the table already exists or was created, FALSE if creation fails.
   *
   * @throws \RuntimeException
   *   Thrown when the variable $this->table is not set.
   */
  protected function ensureTableExists(): bool {
    if (empty($this->table)) {
      throw new \RuntimeException('The variable $this->table is not set.');
    }

    try {
      $schema_definition = static::schemaDefinition();
      $this->connection->schema()->createTable($this->table, $schema_definition);
    }
    // If another process has already created the table, attempting to create
    // it will throw an exception. In this case just catch the exception and do
    // nothing.
    catch (DatabaseException) {
    }
    catch (\Exception) {
      return FALSE;
    }
    return TRUE;
  }

}
