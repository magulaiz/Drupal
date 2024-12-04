<?php

namespace Drupal\Core\Database\Exception;

use Drupal\Core\Database\DatabaseException;
use Drupal\Core\Database\SchemaException;

/**
 * Exception thrown when a database schema object failed to be created.
 */
class SchemaObjectCreationFailureException extends SchemaException implements DatabaseException {
}
