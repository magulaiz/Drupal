<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Entity\ContentEntityTypeInterface;

/**
 * The MongoDB implementation of \Drupal\user\UserStorageSchema.
 */
class UserStorageSchema extends ContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function processIdentifierSchema(&$schema, $key) {
    // The "users" table does not use serial identifiers.
    if ($key != $this->entityType->getKey('id')) {
      parent::processIdentifierSchema($schema, $key);
    }
  }

}
