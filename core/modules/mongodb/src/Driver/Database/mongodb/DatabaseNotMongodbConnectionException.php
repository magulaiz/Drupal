<?php

namespace Drupal\mongodb\Driver\Database\mongodb;

use Drupal\Core\Database\DatabaseException;

/**
 * Exception thrown when the base class of the database connection object is not
 * of the class MongoDB\Database.
 */
class DatabaseNotMongodbConnectionException extends \Exception implements DatabaseException {}
