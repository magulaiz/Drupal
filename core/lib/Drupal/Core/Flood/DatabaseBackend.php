<?php

namespace Drupal\Core\Flood;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the database flood backend. This is the default Drupal backend.
 */
class DatabaseBackend implements FloodInterface, PrefixFloodInterface {

  /**
   * The database table name.
   */
  const TABLE_NAME = 'flood';

  /**
   * Construct the DatabaseBackend.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection which will be used to store the flood event
   *   information.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack used to retrieve the current request.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    protected Connection $connection,
    protected RequestStack $requestStack,
    protected TimeInterface $time,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function register($name, $window = 3600, $identifier = NULL) {
    if (!isset($identifier)) {
      $identifier = $this->requestStack->getCurrentRequest()->getClientIp();
    }
    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function () use ($name, $window, $identifier): int {
        return $this->connection->insert(DatabaseBackend::TABLE_NAME)
          ->fields([
            'event' => $name,
            'identifier' => $identifier,
            'timestamp' => $this->time->getRequestTime(),
            'expiration' => $this->time->getRequestTime() + $window,
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
   * {@inheritdoc}
   */
  public function clear($name, $identifier = NULL) {
    if (!isset($identifier)) {
      $identifier = $this->requestStack->getCurrentRequest()->getClientIp();
    }
    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function () use ($name, $identifier): void {
        $this->connection->delete(DatabaseBackend::TABLE_NAME)
          ->condition('event', $name)
          ->condition('identifier', $identifier)
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
  public function clearByPrefix(string $name, string $prefix): void {
    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function () use ($name, $prefix): void {
        $this->connection->delete(DatabaseBackend::TABLE_NAME)
          ->condition('event', $name)
          ->condition('identifier', $prefix . '-%', 'LIKE')
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
  public function isAllowed($name, $threshold, $window = 3600, $identifier = NULL) {
    if (!isset($identifier)) {
      $identifier = $this->requestStack->getCurrentRequest()->getClientIp();
    }
    return $this->connection->executeEnsuringSchemaOnFailure(
      execute: function () use ($name, $threshold, $window, $identifier): bool {
        $number = $this->connection->select(DatabaseBackend::TABLE_NAME, 'f')
          ->condition('event', $name)
          ->condition('identifier', $identifier)
          ->condition('timestamp', $this->time->getRequestTime() - $window, '>')
          ->countQuery()
          ->execute()
          ->fetchField();
        return ($number < $threshold);
      },
      schema: [
        static::TABLE_NAME => $this->schemaDefinition(),
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): void {
        $this->connection->delete(DatabaseBackend::TABLE_NAME)
          ->condition('expiration', $this->time->getRequestTime(), '<')
          ->execute();
      },
      schema: [
        static::TABLE_NAME => $this->schemaDefinition(),
      ],
    );
  }

  /**
   * Defines the schema for the flood table.
   *
   * @internal
   */
  public function schemaDefinition() {
    return [
      'description' => 'Flood controls the threshold of events, such as the number of contact attempts.',
      'fields' => [
        'fid' => [
          'description' => 'Unique flood event ID.',
          'type' => 'serial',
          'not null' => TRUE,
        ],
        'event' => [
          'description' => 'Name of event (e.g. contact).',
          'type' => 'varchar_ascii',
          'length' => 64,
          'not null' => TRUE,
          'default' => '',
        ],
        'identifier' => [
          'description' => 'Identifier of the visitor, such as an IP address or hostname.',
          'type' => 'varchar_ascii',
          'length' => 128,
          'not null' => TRUE,
          'default' => '',
        ],
        'timestamp' => [
          'description' => 'Timestamp of the event.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'big',
        ],
        'expiration' => [
          'description' => 'Expiration timestamp. Expired events are purged on cron run.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'big',
        ],
      ],
      'primary key' => ['fid'],
      'indexes' => [
        'allow' => ['event', 'identifier', 'timestamp'],
        'purge' => ['expiration'],
      ],
    ];
  }

}
