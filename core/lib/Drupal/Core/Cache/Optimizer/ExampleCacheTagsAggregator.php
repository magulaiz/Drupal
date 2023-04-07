<?php

declare(strict_types=1);

namespace Drupal\Core\Cache\Optimizer;

class ExampleCacheTagsAggregator implements CacheTagsAggregatorInterface {

  public function mapTag(string $tag): string {
    if (str_starts_with($tag, 'node:')) {
      return 'node_list';
    }
    return $tag;
  }

}
