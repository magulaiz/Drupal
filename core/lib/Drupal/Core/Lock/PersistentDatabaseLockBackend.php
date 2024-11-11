<?php

namespace Drupal\Core\Lock;

use Drupal\Core\Database\DatabaseConnectionInterface;
use Drupal\Core\Database\NonTransactionalConnection;

/**
 * Defines the persistent database lock backend.
 *
 * This backend is global for this Drupal installation.
 *
 * @ingroup lock
 */
class PersistentDatabaseLockBackend extends DatabaseLockBackend {

  /**
   * Constructs a new PersistentDatabaseLockBackend.
   *
   * @param \Drupal\Core\Database\DatabaseConnectionInterface $database
   *   The database connection.
   */
  public function __construct(protected DatabaseConnectionInterface $database) {
    // Do not call the parent constructor to avoid registering a shutdown
    // function that releases all the locks at the end of a request.
    if (!($this->database instanceof NonTransactionalConnection) && $this->database->databaseType() !== 'sqlite') {
      @trigger_error('Calling ' . __METHOD__ . '() with a transactional database connection is deprecated in drupal:10.4.0 and will be required in drupal:12.0.0. See https://www.drupal.org/node/3310017', E_USER_DEPRECATED);
    }
    // Set the lockId to a fixed string to make the lock ID the same across
    // multiple requests. The lock ID is used as a page token to relate all the
    // locks set during a request to each other.
    // @see \Drupal\Core\Lock\LockBackendInterface::getLockId()
    $this->lockId = 'persistent';
  }

}
