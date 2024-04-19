<?php

namespace Drupal\Core\Cache\Optimizer;

interface CacheTagsAggregatorInterface {

  public function mapTag(string $tag): string;

}
