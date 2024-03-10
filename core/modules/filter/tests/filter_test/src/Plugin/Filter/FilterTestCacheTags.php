<?php

namespace Drupal\filter_test\Plugin\Filter;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\FilterType;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a test filter to associate cache tags.
 */
#[Filter(
  id: "filter_test_cache_tags",
  title: new TranslatableMarkup("Testing filter"),
  type: FilterType::TransformReversible,
  description: new TranslatableMarkup("Does not change content; associates cache tags.")
)]
class FilterTestCacheTags extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    $result->addCacheTags(['foo:bar']);
    $result->addCacheTags(['foo:baz']);
    return $result;
  }

}
