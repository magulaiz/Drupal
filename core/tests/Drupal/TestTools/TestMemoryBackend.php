<?php

declare(strict_types = 1);

namespace Drupal\TestTools;

use Drupal\Core\Cache\MemoryCounterBackend;

/**
 * Memory backend with access to the values.
 */
class TestMemoryBackend extends MemoryCounterBackend {

  /**
   * Gets all values from the cache.
   *
   * @param bool $allow_invalid
   *   TRUE to include invalid (e.g. expired) values.
   *
   * @return array<string, mixed>
   *   Cached values.
   */
  public function getAllValues($allow_invalid = FALSE): array {
    return array_map(
      static fn (\stdClass $object) => $object->data,
      $this->getAll($allow_invalid),
    );
  }

  /**
   * Gets all cache objects.
   *
   * @param bool $allow_invalid
   *   TRUE to include invalid (e.g. expired) values.
   *
   * @return array<string, \stdClass>
   *   Cached objects.
   */
  public function getAll($allow_invalid = FALSE): array {
    $cids = $this->getAllCids();
    $objects = array_map(
      fn ($cid) => $this->get($cid, $allow_invalid),
      array_combine($cids, $cids),
    );
    return array_filter($objects);
  }

  /**
   * Gets all cache ids.
   *
   * @return list<string>
   *   List of cache ids.
   */
  public function getAllCids(): array {
    return array_keys($this->cache);
  }

}
