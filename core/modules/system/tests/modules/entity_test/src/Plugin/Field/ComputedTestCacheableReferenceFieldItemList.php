<?php

namespace Drupal\entity_test\Plugin\Field;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableDependencyTrait;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * A computed entity reference field item list.
 */
class ComputedTestCacheableReferenceFieldItemList extends EntityReferenceFieldItemList implements CacheableDependencyInterface {

  use ComputedItemListTrait;
  use CacheableDependencyTrait;

  /**
   * Compute the list property from state.
   */
  protected function computeValue() {
    foreach (\Drupal::state()->get('entity_test_reference_computed_target_ids', []) as $delta => $id) {
      $this->list[$delta] = $this->createItem($delta, $id);
    }

    $cacheability = (new CacheableMetadata())
      ->setCacheContexts(['url.query_args:computed_test_cacheable_reference_field'])
      ->setCacheTags(['field:computed_test_cacheable_reference_field'])
      ->setCacheMaxAge(800);

    $this->setCacheability($cacheability);
  }

}
