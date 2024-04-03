<?php

namespace Drupal\Core\Cache\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Represents a "common cache tags" event.
 */
class CommonCacheTagsEvent extends Event {

  /**
   * The common cache tags.
   */
  protected array $tags = [];

  /**
   * Adds cache tags to the list of common cache tags.
   *
   * @param array $tags
   *   The cache tags to add.
   */
  public function addCacheTags(array $tags): void {
    $this->tags = array_unique(array_merge($this->tags, $tags));
  }

  /**
   * Retrieves the list of common cache tags.
   *
   * @return string[]
   *   The common cache tags.
   */
  public function getCacheTags(): array {
    return $this->tags;
  }

}
