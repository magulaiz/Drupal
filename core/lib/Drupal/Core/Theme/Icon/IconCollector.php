<?php

declare(strict_types=1);

namespace Drupal\Core\Theme\Icon;

use Drupal\Core\Cache\CacheCollector;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Lock\LockBackendInterface;

/**
 * A CacheCollector implementation for building used icons info.
 */
class IconCollector extends CacheCollector {

  /**
   * Constructs a IconCollector instance.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend.
   */
  public function __construct(
    CacheBackendInterface $cache,
    LockBackendInterface $lock,
  ) {
    parent::__construct('icon_pack_used', $cache, $lock, ['icon_pack_collector']);
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value): void {
    parent::set($key, $value);
    $this->persist($key);
    static::updateCache();
  }

  /**
   * {@inheritdoc}
   */
  public function resolveCacheMiss($key): mixed {
    return NULL;
  }

}
