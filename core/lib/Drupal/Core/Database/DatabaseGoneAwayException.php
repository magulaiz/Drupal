<?php

namespace Drupal\Core\Database;

/**
 * Exception thrown if connection drops unexpectedly.
 */
class DatabaseGoneAwayException extends \RuntimeException implements DatabaseException {}
