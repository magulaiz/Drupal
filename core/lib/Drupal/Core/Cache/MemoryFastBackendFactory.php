<?php

namespace Drupal\Core\Cache;

use Drupal\Core\Cache\MemoryCache\MemoryCache;

class MemoryFastBackendFactory implements CacheFactoryInterface {

  /**
   * Instantiated memory cache bins.
   *
   * @var \Drupal\Core\Cache\MemoryCache\MemoryCache[]
   */
  protected $bins = [];

  /**
   * {@inheritdoc}
   */
  public function get($bin) {
    if (!isset($this->bins[$bin])) {
      $this->bins[$bin] = new MemoryCache();
    }
    return $this->bins[$bin];
  }

}
