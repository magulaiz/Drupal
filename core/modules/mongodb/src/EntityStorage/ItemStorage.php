<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\aggregator\ItemStorageInterface;

/**
 * The MongoDB implementation of \Drupal\aggregator\ItemStorage.
 */
class ItemStorage extends ContentEntityStorage implements ItemStorageInterface {

  use ItemStorageTrait;

}
