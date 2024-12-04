<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Exception;

use Drupal\Core\Database\DatabaseException;

/**
 * Exception thrown when a connection does not implement transactions.
 */
final class TransactionsNotAllowedException extends \RuntimeException implements DatabaseException {

}
