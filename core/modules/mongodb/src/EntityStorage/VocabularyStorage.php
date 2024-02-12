<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\taxonomy\VocabularyStorageInterface;

/**
 * The MongoDB implementation of \Drupal\taxonomy\VocabularyStorage.
 */
class VocabularyStorage extends ConfigEntityStorage implements VocabularyStorageInterface {

  use VocabularyStorageTrait;

}
