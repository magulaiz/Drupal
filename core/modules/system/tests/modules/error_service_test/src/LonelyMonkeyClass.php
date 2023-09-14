<?php

namespace Drupal\error_service_test;

use Drupal\Core\Database\Connection;

/**
 * A class with a single dependency.
 */
class LonelyMonkeyClass {

  public function __construct(
      /**
       * The database connection.
       */
      protected Connection $connection
  )
  {
  }

}
