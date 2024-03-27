<?php

namespace Drupal\Core\Routing;

/**
 * Extends the router provider interface to provide caching support.
 */
interface CacheableRouteProviderInterface extends RouteProviderInterface {

  /**
   * Adds a cache key part to be used in the cache ID of the route collection.
   *
   * @param string $cache_key_provider
   *   The provider of the cache key part.
   * @param string $cache_key_part
   *   A string to be used as a cache key part.
   */
  public function addExtraCacheKeyPart(string $cache_key_provider, string $cache_key_part);

}
