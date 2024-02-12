<?php

namespace Drupal\mongodb\EntityStorage;

/**
 * Trait for the storage handler class for nodes.
 */
trait NodeStorageTrait {

  /**
   * {@inheritdoc}
   */
  public function updateType($old_type, $new_type) {
    return $this->database->update($this->getBaseTable())
      ->fields(['type' => $new_type])
      ->condition('type', $old_type)
      ->execute();
  }

}
