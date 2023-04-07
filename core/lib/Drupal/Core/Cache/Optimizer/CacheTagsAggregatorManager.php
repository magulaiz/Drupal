<?php

namespace Drupal\Core\Cache\Optimizer;

/**
 * Cache tags aggregator manager.
 *
 * Before being stored in the DatabaseInvalidator, cache tags are piped through
 * services with service tag 'cache_tags_aggregator'.
 * A cache tags aggregator might replace all 'node:*' tags with 'node_list'.
 * Or in the extreme, replace all tags with just one "rendered'.
 * This is to reduce the cost of each tag (a probabilistic fraction of a
 * database query) with the benefit of fine-grained invalidation.
 * Core provides some cache tags aggregators that are good for many.
 * Every Drupal site may disable, change, or add to this, to fine-tune cache tag
 * granularity to their specific requirements.
 */
class CacheTagsAggregatorManager implements CacheTagsAggregatorManagerInterface {

  /**
   * @var array<string, string>
   */
  protected array $map;

  /**
   * @var array<\Drupal\Core\Cache\Optimizer\CacheTagsAggregatorInterface>
   */
  protected array $sortedCacheTagsAggregators = [];

  public function __construct() {
    // @fixme Add service collector logic.
    $this->sortedCacheTagsAggregators[] = new ExampleCacheTagsAggregator();
  }

  public function aggregateTags(array $tags): array {
    $requestedTagsIdentityMap = array_combine($tags, $tags);
    // Collect missing mappings.
    $missingTagsMap = array_diff_key($requestedTagsIdentityMap, $this->map);
    foreach ($this->sortedCacheTagsAggregators as $aggregator) {
      $this->map += array_map($aggregator->mapTag(...), $missingTagsMap);
    }
    return array_values(array_unique(array_intersect_key($this->map, $requestedTagsIdentityMap)));
  }

}
