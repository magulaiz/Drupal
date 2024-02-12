<?php

namespace Drupal\mongodb\EntityStorage;

/**
 * Trait for the storage handler class for taxonomy vocabularies.
 */
trait VocabularyStorageTrait {

  /**
   * {@inheritdoc}
   */
  public function resetCache(array $ids = NULL) {
    drupal_static_reset('taxonomy_vocabulary_get_names');
    parent::resetCache($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function getToplevelTids($vids) {
    $tids = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $vids, 'IN')
      ->condition('parent.target_id', 0)
      ->execute();

    return array_values($tids);
  }

}
