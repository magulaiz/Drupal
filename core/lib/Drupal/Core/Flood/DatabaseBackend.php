<?php

namespace Drupal\Core\Flood;

use Drupal\Core\Database\LazyTableCreationTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Database\Connection;

/**
 * Defines the database flood backend. This is the default Drupal backend.
 */
class DatabaseBackend implements FloodInterface, PrefixFloodInterface {

  use LazyTableCreationTrait;

  /**
   * The database table name.
   *
   * @deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use the
   *    class variable $this->table instead.
   *
   * @see https://www.drupal.org/node/3301744
   */
  const TABLE_NAME = 'flood';

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Construct the DatabaseBackend.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection which will be used to store the flood event
   *   information.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack used to retrieve the current request.
   */
  public function __construct(Connection $connection, RequestStack $request_stack) {
    $this->connection = $connection;
    $this->requestStack = $request_stack;
    $this->table = 'flood';
  }

  /**
   * {@inheritdoc}
   */
  public function register($name, $window = 3600, $identifier = NULL) {
    if (!isset($identifier)) {
      $identifier = $this->requestStack->getCurrentRequest()->getClientIp();
    }
    $try_again = FALSE;
    try {
      $this->doInsert($name, $window, $identifier);
    }
    catch (\Exception $e) {
      $try_again = $this->ensureTableExists();
      if (!$try_again) {
        throw $e;
      }
    }
    if ($try_again) {
      $this->doInsert($name, $window, $identifier);
    }
  }

  /**
   * Inserts an event into the flood table.
   *
   * @param string $name
   *   The name of an event.
   * @param int $window
   *   Number of seconds before this event expires.
   * @param string $identifier
   *   Unique identifier of the current user.
   *
   * @see \Drupal\Core\Flood\DatabaseBackend::register
   */
  protected function doInsert($name, $window, $identifier) {
    $this->connection->insert($this->table)
      ->fields([
        'event' => $name,
        'identifier' => $identifier,
        'timestamp' => REQUEST_TIME,
        'expiration' => REQUEST_TIME + $window,
      ])
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function clear($name, $identifier = NULL) {
    if (!isset($identifier)) {
      $identifier = $this->requestStack->getCurrentRequest()->getClientIp();
    }
    try {
      $this->connection->delete($this->table)
        ->condition('event', $name)
        ->condition('identifier', $identifier)
        ->execute();
    }
    catch (\Exception $e) {
      $this->catchException($e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearByPrefix(string $name, string $prefix): void {
    try {
      $this->connection->delete($this->table)
        ->condition('event', $name)
        ->condition('identifier', $prefix . '-%', 'LIKE')
        ->execute();
    }
    catch (\Exception $e) {
      $this->catchException($e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isAllowed($name, $threshold, $window = 3600, $identifier = NULL) {
    if (!isset($identifier)) {
      $identifier = $this->requestStack->getCurrentRequest()->getClientIp();
    }
    try {
      $number = $this->connection->select($this->table, 'f')
        ->condition('event', $name)
        ->condition('identifier', $identifier)
        ->condition('timestamp', REQUEST_TIME - $window, '>')
        ->countQuery()
        ->execute()
        ->fetchField();
      return ($number < $threshold);
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
    try {
      $this->connection->delete($this->table)
        ->condition('expiration', REQUEST_TIME, '<')
        ->execute();
    }
    catch (\Exception $e) {
      $this->catchException($e);
    }
  }

  /**
   * {@inheritdoc}
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
