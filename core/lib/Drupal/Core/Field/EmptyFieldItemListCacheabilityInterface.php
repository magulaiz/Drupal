<?php

namespace Drupal\Core\Field;

use Drupal\Core\Cache\CacheableMetadata;

/**
 * Interface for field item lists which have cacheable metadata for empty state.
 *
 * Field item lists should implement this interface when their being empty
 * results from a condition with cacheability distinct from its parent. For
 * instance, computed fields may change their value due to entity CRUD events
 * orthogonal to the parent entity itself. Non-empty lists would continue to
 * yield cacheable metadata (e.g., during serialization) via their items.
 */
interface EmptyFieldItemListCacheabilityInterface {

  /**
   * Get the field item list's cacheable metadata for its empty state.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   Cacheable metadata.
   */
  public function getEmptyListCacheability(): CacheableMetadata;

}
