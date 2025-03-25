<?php

namespace Drupal\Core\Cache;

/**
 * Collects cache clearable services to be called in priority order.
 */
class CacheClearer {

  /**
   * @var \Drupal\Core\Cache\CacheClearableInterface[]
   */
  protected array $cacheClearers = [];

  public function add(CacheClearableInterface $cacheClearer): void {
    $this->cacheClearers[] = $cacheClearer;
  }

  /**
   * Clear Caches.
   */
  public function clearCache(): void {
    foreach ($this->cacheClearers as $cacheClearer) {
      $cacheClearer->clearCache();
    }
  }

}
