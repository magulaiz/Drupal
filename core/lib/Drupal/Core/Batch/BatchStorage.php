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
    $batch = $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): array|FALSE {
        $batch = $this->connection->select('batch', 'b')
          ->fields('b', ['batch'])
          ->condition('bid', $id)
          ->condition('token', $this->csrfToken->get($id))
          ->execute()
          ->fetchField();
      },
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
      execute: function (): void {
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
      execute: function (): void {
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
    return $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): int {
        return $this->connection->insert('batch')
          ->fields([
            'timestamp' => $this->time->getRequestTime(),
            'token' => '',
            'batch' => NULL,
          ])
          ->execute();
      },
      schema: [
        static::TABLE_NAME => $this->schemaDefinition(),
      ],
      retryAfterSchemaEnsured: TRUE,
    );
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
