<?php

namespace Drupal\filter_test\Plugin\Filter;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\FilterType;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Provides a test filter to merge with CacheableMetadata.
 */
#[Filter(
  id: "filter_test_cache_merge",
  title: new TranslatableMarkup("Testing filter"),
  type: FilterType::TransformReversible,
  description: new TranslatableMarkup("Does not change content; merges cacheable metadata.")
)]
class FilterTestCacheMerge extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    $metadata = new CacheableMetadata();
    $metadata->addCacheTags(['merge:tag']);
    $metadata->addCacheContexts(['user.permissions']);
    $result = $result->merge($metadata);

    return $result;
  }

}
