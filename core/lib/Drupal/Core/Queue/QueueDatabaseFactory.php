<?php

namespace Drupal\Core\Queue;

use Drupal\Core\Database\DatabaseConnectionInterface;
use Drupal\Core\Database\NonTransactionalConnection;

/**
 * Defines the queue factory for the database backend.
 */
class QueueDatabaseFactory implements QueueFactoryInterface {

  /**
   * Constructs this factory object.
   *
   * @param \Drupal\Core\Database\DatabaseConnectionInterface $connection
   *   The Connection object containing the queue table.
   */
  public function __construct(protected DatabaseConnectionInterface $connection) {
    if (!($connection instanceof NonTransactionalConnection) && $connection->databaseType() !== 'sqlite') {
      @trigger_error('Calling ' . __METHOD__ . '() with a transactional database connection is deprecated in drupal:10.4.0 and will be required in drupal:12.0.0. See https://www.drupal.org/node/3310017', E_USER_DEPRECATED);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    return new DatabaseQueue($name, $this->connection);
  }

}
