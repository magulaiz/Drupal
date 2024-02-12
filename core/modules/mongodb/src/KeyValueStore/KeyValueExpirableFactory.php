<?php

namespace Drupal\mongodb\KeyValueStore;

use Drupal\Core\KeyValueStore\KeyValueExpirableFactory as CoreKeyValueExpirableFactory;

/**
 * The MongoDB implementation of \Drupal\Core\KeyValueStore\KeyValueExpirableFactory.
 */
class KeyValueExpirableFactory extends CoreKeyValueExpirableFactory {

  const DEFAULT_SERVICE = 'mongodb.keyvalue.expirable.database';

}
