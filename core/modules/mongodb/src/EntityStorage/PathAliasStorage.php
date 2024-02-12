<?php

namespace Drupal\mongodb\EntityStorage;

/**
 * Defines the storage handler class for path_alias entities.
 */
class PathAliasStorage extends ContentEntityStorage {

  /**
   * {@inheritdoc}
   */
  public function createWithSampleValues($bundle = FALSE, array $values = []) {
    $entity = parent::createWithSampleValues($bundle, ['path' => '/<front>'] + $values);
    // Ensure the alias is only 255 characters long.
    $entity->set('alias', substr('/' . $entity->get('alias')->value, 0, 255));
    return $entity;
  }

}
