<?php

namespace Drupal\Core\Batch;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class BatchStorage implements BatchStorageInterface {

  /**
   * The table name.
   */
  const TABLE_NAME = 'batch';

  /**
   * Constructs the database batch storage service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrfToken
   *   The CSRF token generator.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    protected Connection $connection,
    protected SessionInterface $session,
    protected CsrfTokenGenerator $csrfToken,
    protected TimeInterface $time,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    // Ensure that a session is started before using the CSRF token generator.
    $this->session->start();
    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function () use ($id): string|FALSE {
        return $this->connection->select('batch', 'b')
          ->fields('b', ['batch'])
          ->condition('bid', $id)
          ->condition('token', $this->csrfToken->get($id))
          ->execute()
          ->fetchField();
      },
      returnValue: $batch,
      schema: [
        static::TABLE_NAME => $this->schemaDefinition(),
      ],
    );
    if ($batch) {
      return unserialize($batch);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($id) {
    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function () use ($id): void {
        $this->connection->delete('batch')
          ->condition('bid', $id)
          ->execute();
      },
      schema: [
        static::TABLE_NAME => $this->schemaDefinition(),
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function update(array $batch) {
    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function () use ($batch): void {
        $this->connection->update('batch')
          ->fields(['batch' => serialize($batch)])
          ->condition('bid', $batch['id'])
          ->execute();
      },
      schema: [
        static::TABLE_NAME => $this->schemaDefinition(),
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function cleanup() {
    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): void {
        // Cleanup the batch table and the queue for failed batches.
        $this->connection->delete('batch')
          ->condition('timestamp', $this->time->getRequestTime() - 864000, '<')
          ->execute();
      },
      schema: [
        static::TABLE_NAME => $this->schemaDefinition(),
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $batch) {
    // Ensure that a session is started before using the CSRF token generator,
    // and update the database record.
    $this->session->start();
    $this->connection->update('batch')
      ->fields([
        'token' => $this->csrfToken->get($batch['id']),
        'batch' => serialize($batch),
      ])
      ->condition('bid', $batch['id'])
      ->execute();
  }

  /**
   * Returns a new batch id.
   *
   * @return int
   *   A batch id.
   */
  public function getId(): int {
    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): int {
        return $this->connection->insert('batch')
          ->fields([
            'timestamp' => $this->time->getRequestTime(),
            'token' => '',
            'batch' => NULL,
          ])
          ->execute();
      },
      returnValue: $id,
      schema: [
        static::TABLE_NAME => $this->schemaDefinition(),
      ],
      retryAfterSchemaEnsured: TRUE,
    );

    return $id;
  }

  /**
   * Inserts a record in the table and returns the batch id.
   *
   * @return int
   *   A batch id.
   *
   * @deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use
   * \Drupal\Core\Database\Connection::executeEnsuringSchemaOnFailure()
   * instead.
   *
   * @see https://www.drupal.org/node/3489185
   */
  protected function doInsertBatchRecord(): int {
    @trigger_error(__METHOD__ . '() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use \Drupal\Core\Database\Connection::executeEnsuringSchemaOnFailure() instead. See https://www.drupal.org/node/3489185', E_USER_DEPRECATED);
    return $this->connection->insert('batch')
      ->fields([
        'timestamp' => $this->time->getRequestTime(),
        'token' => '',
        'batch' => NULL,
      ])
      ->execute();
  }

  /**
   * Check if the table exists and create it if not.
   *
   * @deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use
   * \Drupal\Core\Database\Connection::executeEnsuringSchemaOnFailure()
   * instead.
   *
   * @see https://www.drupal.org/node/3489185
   */
  protected function ensureTableExists() {
    @trigger_error(__METHOD__ . '() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use \Drupal\Core\Database\Connection::executeEnsuringSchemaOnFailure() instead. See https://www.drupal.org/node/3489185', E_USER_DEPRECATED);
    try {
      $database_schema = $this->connection->schema();
      $schema_definition = $this->schemaDefinition();
      $database_schema->createTable(static::TABLE_NAME, $schema_definition);
    }
    // If another process has already created the batch table, attempting to
    // recreate it will throw an exception. In this case just catch the
    // exception and do nothing.
    catch (DatabaseException) {
    }
    catch (\Exception) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Act on an exception when batch might be stale.
   *
   * If the table does not yet exist, that's fine, but if the table exists and
   * yet the query failed, then the batch is stale and the exception needs to
   * propagate.
   *
   * @param $e
   *   The exception.
   *
   * @throws \Exception
   *
   * @deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use
   * \Drupal\Core\Database\Connection::executeEnsuringSchemaOnFailure()
   * instead.
   *
   * @see https://www.drupal.org/node/3489185
   */
  protected function catchException(\Exception $e) {
    @trigger_error(__METHOD__ . '() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use \Drupal\Core\Database\Connection::executeEnsuringSchemaOnFailure() instead. See https://www.drupal.org/node/3489185', E_USER_DEPRECATED);
    if ($this->connection->schema()->tableExists(static::TABLE_NAME)) {
      throw $e;
    }
  }

  /**
   * Defines the schema for the batch table.
   *
   * @internal
   */
  public function schemaDefinition() {
    return [
      'description' => 'Stores details about batches (processes that run in multiple HTTP requests).',
      'fields' => [
        'bid' => [
          'description' => 'Primary Key: Unique batch ID.',
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'token' => [
          'description' => "A string token generated against the current user's session id and the batch id, used to ensure that only the user who submitted the batch can effectively access it.",
          'type' => 'varchar_ascii',
          'length' => 64,
          'not null' => TRUE,
        ],
        'timestamp' => [
          'description' => 'A Unix timestamp indicating when this batch was submitted for processing. Stale batches are purged at cron time.',
          'type' => 'int',
          'not null' => TRUE,
        ],
        'batch' => [
          'description' => 'A serialized array containing the processing data for the batch.',
          'type' => 'blob',
          'not null' => FALSE,
          'size' => 'big',
        ],
      ],
      'primary key' => ['bid'],
      'indexes' => [
        'token' => ['token'],
      ],
    ];
  }

}
