<?php

namespace Drupal\Core\Queue;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseConnectionInterface;

/**
 * Defines the queue factory for the database backend.
 */
class QueueDatabaseFactory implements QueueFactoryInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\DatabaseConnectionInterface
   */
  protected $connection;

  /**
   * Constructs this factory object.
   *
   * @param \Drupal\Core\Database\Connection|\Drupal\Core\Database\DatabaseConnectionInterface $connection
   *   The Connection object containing the queue table.
   */
  public function __construct(Connection|DatabaseConnectionInterface $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    return new DatabaseQueue($name, $this->connection);
  }

}
