<?php

namespace Drupal\mongodb\KeyValueStore;

use Drupal\Core\KeyValueStore\DatabaseStorageExpirable as CoreDatabaseStorageExpirable;
use Drupal\mongodb\Driver\Database\mongodb\Statement;

/**
 * The MongoDB implementation of \Drupal\Core\KeyValueStore\DatabaseStorageExpirable.
 */
class DatabaseStorageExpirable extends CoreDatabaseStorageExpirable {

  use DatabaseStorageTrait;

  /**
   * Indicator for the existence of the database table.
   *
   * @var bool
   */
  protected $tableExists = FALSE;

  /**
   * {@inheritdoc}
   */
  public function has($key) {
    $prefixed_table = $this->connection->getMongodbPrefixedTable($this->table);
    $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
      ['collection' => ['$eq' => (string) $this->collection], 'expire' => ['$gt' => REQUEST_TIME], 'name' => ['$eq' => (string) $key]],
      ['projection' => ['_id' => 1]]
    );

    if ($cursor && !empty($cursor->toArray())) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $keys) {
    foreach ($keys as &$key) {
      $key = (string) $key;
    }
    $prefixed_table = $this->connection->getMongodbPrefixedTable($this->table);
    $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
      ['collection' => ['$eq' => (string) $this->collection], 'expire' => ['$gt' => REQUEST_TIME], 'name' => ['$in' => $keys]],
      ['projection' => ['name' => 1, 'value' => 1, '_id' => 0]]
    );

    $statement = new Statement($this->connection, $cursor, ['name', 'value']);
    $values = $statement->execute()->fetchAllKeyed();

    return array_map([$this->serializer, 'decode'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function getAll() {
    $prefixed_table = $this->connection->getMongodbPrefixedTable($this->table);
    $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
      ['collection' => ['$eq' => (string) $this->collection], 'expire' => ['$gt' => REQUEST_TIME]],
      ['projection' => ['name' => 1, 'value' => 1, '_id' => 0]]
    );

    $statement = new Statement($this->connection, $cursor, ['name', 'value']);
    $values = $statement->execute()->fetchAllKeyed();

    return array_map([$this->serializer, 'decode'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function setWithExpire($key, $value, $expire) {
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    parent::setWithExpire((string) $key, $value, (int) $expire);
  }

  /**
   * {@inheritdoc}
   */
  public function setWithExpireIfNotExists($key, $value, $expire) {
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    return parent::setWithExpireIfNotExists((string) $key, $value, (int) $expire);
  }

}
