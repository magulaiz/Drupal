<?php

namespace Drupal\mongodb\KeyValueStore;

use Drupal\Core\KeyValueStore\DatabaseStorage as CoreDatabaseStorage;
use Drupal\mongodb\Driver\Database\mongodb\Statement;

/**
 * The MongoDB implementation of \Drupal\Core\KeyValueStore\DatabaseStorage.
 */
class DatabaseStorage extends CoreDatabaseStorage {

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
      ['collection' => ['$eq' => (string) $this->collection], 'name' => ['$eq' => (string) $key]],
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
    if (empty($keys)) {
      return [];
    }
    foreach ($keys as &$key) {
      $key = (string) $key;
    }

    $prefixed_table = $this->connection->getMongodbPrefixedTable($this->table);
    $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
      ['collection' => ['$eq' => (string) $this->collection], 'name' => ['$in' => $keys]],
      ['projection' => ['name' => 1, 'value' => 1, '_id' => 0]]
    );

    $statement = new Statement($this->connection, $cursor, ['name', 'value']);
    $result = $statement->execute()->fetchAllAssoc('name');

    $values = [];
    foreach ($keys as $key) {
      if (isset($result[$key])) {
        $values[$key] = $this->serializer->decode($result[$key]->value);
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getAll() {
    $prefixed_table = $this->connection->getMongodbPrefixedTable($this->table);
    $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
      ['collection' => ['$eq' => (string) $this->collection]],
      ['projection' => ['name' => 1, 'value' => 1, '_id' => 0]]
    );

    $statement = new Statement($this->connection, $cursor, ['name', 'value']);
    $result = $statement->execute();

    $values = [];
    foreach ($result as $item) {
      if ($item) {
        $values[$item->name] = $this->serializer->decode($item->value);
      }
    }
    return $values;
  }

}
