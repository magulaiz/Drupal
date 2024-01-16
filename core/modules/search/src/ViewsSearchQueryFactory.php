<?php

declare(strict_types=1);

namespace Drupal\search;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;

/**
 * Select extender factory for views search queries.
 */
class ViewsSearchQueryFactory {

  /**
   * Constructs a ViewsSearchQueryFactory object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\search\SearchTextProcessorInterface $searchTextProcessor
   *   The search text processor service.
   */
  public function __construct(
    protected readonly ConfigFactoryInterface $configFactory,
    protected readonly SearchTextProcessorInterface $searchTextProcessor,
  ) {
  }

  /**
   * Returns a query extender for views search queries.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   Select query object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   *
   * @return Drupal\search\ViewsSearchQuery
   *   A query extender for views search queries.
   */
  public function get(SelectInterface $query, Connection $connection): ViewsSearchQuery {
    // @todo remove this BC layer in drupal:11.0.0.
    $class = $connection->getConnectionOptions()['namespace'] . '\\ViewsSearchQuery';
    if (class_exists($class)) {
      @trigger_error("Invoking {$class} outside of a backend overridable service is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Include the driver class in a backend overridable service instead. See https://www.drupal.org/node/3217534", E_USER_DEPRECATED);
      return new $class($query, $connection);
    }
    // @todo end
    return new ViewsSearchQuery($query, $connection, $this->configFactory, $this->searchTextProcessor);
  }

}
