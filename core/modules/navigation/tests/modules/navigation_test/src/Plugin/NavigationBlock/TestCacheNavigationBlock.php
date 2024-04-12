<?php

namespace Drupal\navigation_test\Plugin\NavigationBlock;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Attribute\NavigationBlock;
use Drupal\navigation\NavigationBlockPluginBase;

/**
 * Provides a navigation block to test caching.
 */
#[NavigationBlock(
  id: "test_cache",
  admin_label: new TranslatableMarkup("Test block caching")
)]
class TestCacheNavigationBlock extends NavigationBlockPluginBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // @phpstan-ignore-next-line
    $content = \Drupal::state()->get('navigation_test.content');

    $build = [];
    if (!empty($content)) {
      $build['#markup'] = $content;
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // @phpstan-ignore-next-line
    return \Drupal::state()->get('navigation_test.cache_contexts', []);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // @phpstan-ignore-next-line
    return \Drupal::state()->get('navigation_test.cache_max_age', parent::getCacheMaxAge());
  }

}
