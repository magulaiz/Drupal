<?php

namespace Drupal\mongodb\KeyValueStore;

use MongoDB\BSON\Binary;

/**
 * The MongoDB implementation for the KeyValue database storage trait.
 */
trait DatabaseStorageTrait {

  /**
   * {@inheritdoc}
   */
  public function set($key, $value) {
    // The key_value table in created during the install of the system module.
    // The MongoDB module is installed before the system module. And during the
    // install process of a module there is data written to the key_value table.
    // MongoDB creates the table on data insert and the table will not be known
    // to the table information service. This is not acceptable.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    return parent::set((string) $key, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function setIfNotExists($key, $value) {
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    return parent::setIfNotExists((string) $key, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function rename($key, $new_key) {
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    parent::rename((string) $key, (string) $new_key);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $keys) {
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    foreach ($keys as &$key) {
      $key = (string) $key;
    }

    parent::deleteMultiple($keys);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    parent::deleteAll();
  }

}
