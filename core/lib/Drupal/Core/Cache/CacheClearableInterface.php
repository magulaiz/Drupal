<?php

namespace Drupal\Core\Cache;

/**
 * Provides an interface for services with clearable caches.
 */
interface CacheClearableInterface {

  /**
   * Clears the cache.
   */
  public function clearCache(): void;

}
