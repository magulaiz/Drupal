<?php

namespace Drupal\mongodb\Driver\Database\mongodb;

use Drupal\Core\Database\DatabaseException;

/**
 * Exception thrown if an query containing SQL is run against a MongoDB database.
 */
class MongodbSQLException extends \Exception implements DatabaseException {}
