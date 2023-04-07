<?php

declare(strict_types=1);

namespace Drupal\Core\Cache\Optimizer;

interface CacheTagsAggregatorManagerInterface {

  /**
   * Aggregate tags.
   *
   * @param list<string> $tags;
   * @return list<string>
   */
  public function aggregateTags(array $tags): array;

}
