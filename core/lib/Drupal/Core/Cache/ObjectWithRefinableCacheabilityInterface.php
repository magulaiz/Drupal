<?php

namespace Drupal\Core\Cache;

/**
 * Allows cacheability metadata to be added for the current runtime.
 */
interface ObjectWithRefinableCacheabilityInterface {

  /**
   * Adds a dependency on an object: merges its cacheability metadata.
   *
   * @param \Drupal\Core\Cache\CacheableDependencyInterface|object $other_object
   *   The dependency. If the object implements CacheableDependencyInterface,
   *   then its cacheability metadata will be used. Otherwise, the passed in
   *   object must be assumed to be uncacheable, so max-age 0 is set.
   *
   * @return $this
   *
   * @see \Drupal\Core\Cache\CacheableMetadata::createFromObject()
   */
  public function addCacheableDependency($other_object);

}
