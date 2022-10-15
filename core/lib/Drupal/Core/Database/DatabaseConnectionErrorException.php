<?php

namespace Drupal\Core\Database;

/**
 * Exception thrown if host lookup fails.
 */
class DatabaseConnectionErrorException extends \RuntimeException implements DatabaseException {}
