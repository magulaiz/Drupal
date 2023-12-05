<?php

namespace Drupal\mysql\Driver\Database\mysql;

use Drupal\Core\Database\DatabaseException;

/**
 * Exception thrown if the isolation level is not valid.
 *
 * @see \Drupal\mysql\Driver\Database\mysql\Connection::open()
 */
class DatabaseTransactionIsolationLevelException extends \RuntimeException implements DatabaseException {

  /**
   * Constructs a DatabaseTransactionIsolationLevelException.
   *
   * @param string $isolation_level
   *   The invalid isolation level.
   */
  public function __construct(string $isolation_level) {
    $message = sprintf('The isolation level %s is not valid, use one of options available instead.', $isolation_level);
    parent::__construct($message);
  }

}
