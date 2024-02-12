<?php

namespace Drupal\mongodb\Routing;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Routing\RouteProvider as CoreRouteProvider;
use Drupal\mongodb\Driver\Database\mongodb\Statement;
use Symfony\Component\Routing\RouteCollection;

/**
 * The MongoDB implementation of \Drupal\Core\Routing\RouteProvider.
 */
class RouteProvider extends CoreRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function preLoadRoutes($names) {
//dump('preLoadRoutes');
//dump($names);
    if (empty($names)) {
      throw new \InvalidArgumentException('You must specify the route names to load');
    }

    $routes_to_load = array_diff($names, array_keys($this->routes), array_keys($this->serializedRoutes));
    if ($routes_to_load) {

      $cid = static::ROUTE_LOAD_CID_PREFIX . hash('sha512', serialize($routes_to_load));
      if ($cache = $this->cache->get($cid)) {
        $routes = $cache->data;
      }
      else {
        try {
          $prefixed_table = $this->connection->getMongodbPrefixedTable($this->tableName);
          $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
            ['name' => ['$in' => $routes_to_load]],
            ['projection' => ['name' => 1, 'route' => 1, '_id' => 0]]
          );

          $statement = new Statement($this->connection, $cursor, ['name', 'route']);
          $routes = $statement->execute()->fetchAllKeyed();
//          $result = $this->connection->query('SELECT [name], [route] FROM {' . $this->connection->escapeTable($this->tableName) . '} WHERE [name] IN ( :names[] )', [':names[]' => $routes_to_load]);
//          $routes = $result->fetchAllKeyed();

          $this->cache->set($cid, $routes, Cache::PERMANENT, ['routes']);
        }
        catch (\Exception $e) {
          $routes = [];
        }
      }

      $this->serializedRoutes += $routes;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getRoutesByPath($path) {
//dump('getRoutesByPath');
//dump($path);
    $parts = preg_split('@/+@', mb_strtolower($path), NULL, PREG_SPLIT_NO_EMPTY);

    $collection = new RouteCollection();

    $ancestors = $this->getCandidateOutlines($parts);
    if (empty($ancestors)) {
      return $collection;
    }

    try {
      $prefixed_table = $this->connection->getMongodbPrefixedTable($this->tableName);
      $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
        ['pattern_outline' => ['$in' => $ancestors], 'number_parts' => ['$gte' => count($parts)]],
        ['projection' => ['name' => 1, 'route' => 1, 'fit' => 1, '_id' => 0]]
      );

      $statement = new Statement($this->connection, $cursor, ['name', 'route', 'fit']);
      $routes = $statement->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }
    catch (\Exception $e) {
      $routes = [];
    }

    // We sort by fit and name in PHP to avoid a SQL filesort and avoid any
    // difference in the sorting behavior of SQL back-ends.
    usort($routes, [$this, 'routeProviderRouteCompare']);

    foreach ($routes as $row) {
      $collection->add($row['name'], unserialize($row['route']));
    }

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoutesPaged($offset, $length = NULL) {
//dump('getRoutesPaged');
//dump($offset);
//dump($length);
    // The problem for MongoDB is that setting the length to the number zero is
    // a special case and is equivalent to setting no length.
    if (isset($length) && $length == 0) {
      return [];
    }

    return parent::getRoutesPaged($offset, $length);
  }

}
