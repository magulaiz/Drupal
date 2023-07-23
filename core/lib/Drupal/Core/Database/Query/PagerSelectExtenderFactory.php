<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Query;

use Drupal\Core\Database\Connection;
use Drupal\Core\Pager\PagerManagerInterface;

/**
 * Select extender factory for pager queries.
 */
class PagerSelectExtenderFactory {

  /**
   * Constructs a PagerSelectExtenderFactory object.
   *
   * @param \Drupal\Core\Pager\PagerManagerInterface $pagerManager
   *   The pager manager service.
   */
  public function __construct(
    protected readonly PagerManagerInterface $pagerManager,
  ) {
  }

  /**
   * Returns a query extender for pager queries.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   Select query object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   *
   * @return \Drupal\Core\Database\Query\PagerSelectExtender
   *   A query extender for pager queries.
   */
  public function get(SelectInterface $query, Connection $connection): PagerSelectExtender {
    // @todo remove this BC layer in drupal:11.0.0.
    $class = $connection->getConnectionOptions()['namespace'] . '\\PagerSelectExtender';
    if (class_exists($class)) {
      @trigger_error("Invoking {$class} outside of a backend overridable service is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Include the driver class in a backend overridable service instead. See https://www.drupal.org/node/3217534", E_USER_DEPRECATED);
      return new $class($query, $connection);
    }
    // @todo end
    return new PagerSelectExtender($query, $connection, $this->pagerManager);
  }

}
