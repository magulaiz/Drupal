<?php

declare(strict_types=1);

namespace Drupal\Core\Database;

/**
 * Interface for database connections.
 */
interface DatabaseConnectionInterface {

  /**
   * Opens a client connection.
   *
   * @param array $connection_options
   *   The database connection settings array.
   *
   * @return object
   *   A client connection object.
   */
  public static function open(array &$connection_options = []);

}
