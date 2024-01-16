<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Query;

use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Select extender factory for tablesort queries.
 */
class TableSortExtenderFactory {

  /**
   * Constructs a TableSortExtenderFactory object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(
    protected readonly RequestStack $requestStack,
  ) {
  }

  /**
   * Returns a query extender for tablesort queries.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   Select query object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   *
   * @return \Drupal\Core\Database\Query\TableSortExtender
   *   A query extender for tablesort queries.
   */
  public function get(SelectInterface $query, Connection $connection): TableSortExtender {
    // @todo remove this BC layer in drupal:11.0.0.
    $class = $connection->getConnectionOptions()['namespace'] . '\\TableSortExtender';
    if (class_exists($class)) {
      @trigger_error("Invoking {$class} outside of a backend overridable service is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Include the driver class in a backend overridable service instead. See https://www.drupal.org/node/3217534", E_USER_DEPRECATED);
      return new $class($query, $connection);
    }
    // @todo end
    return new TableSortExtender($query, $connection, $this->requestStack);
  }

}
