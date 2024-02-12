<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Entity\EntityInterface;

/**
 * The MongoDB implementation of \Drupal\media\MediaStorage.
 */
class MediaStorage extends ContentEntityStorage {

  use MediaStorageTrait;

}
