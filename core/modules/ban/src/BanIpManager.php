<?php

namespace Drupal\ban;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseException;

/**
 * Ban IP manager.
 */
class BanIpManager implements BanIpManagerInterface {

  /**
   * The database connection used to check the IP against.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a BanIpManager object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection which will be used to check the IP against.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Check if the ban_ip table exists and create it if not.
   */
  protected function ensureTableExists() {
    try {
      $database_schema = $this->connection->schema();
      $schema_definition = $this->schemaDefinition();
      $database_schema->createTable('ban_ip', $schema_definition);
    }
    // If another process has already created the ban_ip table, attempting to
    // recreate it will throw an exception. In this case just catch the
    // exception and do nothing.
    catch (DatabaseException) {
    }

    return TRUE;
  }

  /**
   * Defines the schema for the {ban_ip} table.
   *
   * @internal
   */
  public function schemaDefinition() {
    $schema = [
      'description' => 'Stores banned IP addresses.',
      'fields' => [
        'iid' => [
          'description' => 'Primary Key: unique ID for IP addresses.',
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'ip' => [
          'description' => 'IP address',
          'type' => 'varchar_ascii',
          'length' => 40,
          'not null' => TRUE,
          'default' => '',
        ],
      ],
      'indexes' => [
        'ip' => ['ip'],
      ],
      'primary key' => ['iid'],
    ];
    return $schema;
  }

  protected function executeWithTableCheck(callable $callback, ...$args) {
    try {
      return $callback(...$args);
    }
    catch (\Exception $e) {
      // If there was an exception, try to create the table.
      if ($this->ensureTableExists()) {
        return $callback(...$args);
      }
      // Some other failure that we can not recover from.
      throw $e;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isBanned($ip) {
    return $this->executeWithTableCheck(function ($ip) {
      return (bool) $this->connection->query("SELECT * FROM {ban_ip} WHERE [ip] = :ip", [':ip' => $ip])->fetchField();
    }, $ip);
  }

  /**
   * {@inheritdoc}
   */
  public function findAll() {
    return $this->executeWithTableCheck(function () {
      return $this->connection->query('SELECT * FROM {ban_ip}');
    });
  }

  /**
   * {@inheritdoc}
   */
  public function banIp($ip) {
    $this->executeWithTableCheck(function ($ip) {
      $this->connection->merge('ban_ip')
        ->key(['ip' => $ip])
        ->fields(['ip' => $ip])
        ->execute();
    }, $ip);
  }

  /**
   * {@inheritdoc}
   */
  public function unbanIp($id) {
    $this->executeWithTableCheck(function ($id) {
      $this->connection->delete('ban_ip')
        ->condition('ip', $id)
        ->execute();
    }, $id);
  }

  /**
   * {@inheritdoc}
   */
  public function findById($ban_id) {
    return $this->executeWithTableCheck(function ($ban_id) {
      return $this->connection->query("SELECT [ip] FROM {ban_ip} WHERE [iid] = :iid", [':iid' => $ban_id])->fetchField();
    }, $ban_id);
  }

}
