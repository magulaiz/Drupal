<?php

declare(strict_types=1);

namespace Drupal\search;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;

/**
 * Select extender factory for search queries.
 */
class SearchQueryFactory {

  /**
   * Constructs a SearchQueryFactory object.
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
   * Returns a query extender for search queries.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   Select query object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   *
   * @return Drupal\search\SearchQuery
   *   A query extender for search queries.
   */
  public function get(SelectInterface $query, Connection $connection): SearchQuery {
    // @todo remove this BC layer in drupal:11.0.0.
    $class = $connection->getConnectionOptions()['namespace'] . '\\SearchQuery';
    if (class_exists($class)) {
      @trigger_error("Invoking {$class} outside of a backend overrideable service is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Include the driver class in a backend overrideable service instead. See https://www.drupal.org/node/3217534", E_USER_DEPRECATED);
      return new $class($query, $connection);
    }
    // @todo end
    return new SearchQuery($query, $connection, $this->configFactory, $this->searchTextProcessor);
  }

}
