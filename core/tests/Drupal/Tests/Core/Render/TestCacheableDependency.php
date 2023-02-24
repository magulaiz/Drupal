<?php

namespace Drupal\Tests\Core\Render;

use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Cacheable dependency object for use in tests.
 */
class TestCacheableDependency implements CacheableDependencyInterface {

  public function __construct(protected array $contexts, protected array $tags, protected $maxAge)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return $this->contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return $this->maxAge;
  }

}
