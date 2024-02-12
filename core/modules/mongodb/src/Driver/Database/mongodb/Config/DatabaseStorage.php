<?php

namespace Drupal\mongodb\Driver\Database\mongodb\Config;

use Drupal\Core\Config\DatabaseStorage as CoreDatabaseStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\mongodb\Driver\Database\mongodb\Statement;

/**
 * The MongoDB implementation for database storage for the Config.
 */
class DatabaseStorage extends CoreDatabaseStorage {

  /**
   * Indicator for the existence of the database table.
   *
   * @var bool
   */
  protected $tableExists = FALSE;

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    $prefixed_table = $this->connection->getMongodbPrefixedTable($this->table);
    $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
      ['collection' => ['$eq' => $this->collection], 'name' => ['$eq' => $name]],
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
  public function read($name) {
    $prefixed_table = $this->connection->getMongodbPrefixedTable($this->table);
    $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
      ['collection' => ['$eq' => $this->collection], 'name' => ['$eq' => $name]],
      ['projection' => ['data' => 1, '_id' => 0]]
    );

    $statement = new Statement($this->connection, $cursor, ['data']);
    $raw = $statement->execute()->fetchField();
    if ($raw !== FALSE) {
      return $this->decode($raw);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function readMultiple(array $names) {
    $prefixed_table = $this->connection->getMongodbPrefixedTable($this->table);
    $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
      ['collection' => ['$eq' => $this->collection], 'name' => ['$in' => $names]],
      ['projection' => ['name' => 1, 'data' => 1, '_id' => 0]]
    );

    $statement = new Statement($this->connection, $cursor, ['name', 'data']);
    $list = $statement->execute()->fetchAllKeyed();
    foreach ($list as &$data) {
      $data = $this->decode($data);
    }

    return $list;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::decode().
   *
   * @throws ErrorException
   *   The unserialize() call will trigger E_NOTICE if the string cannot
   *   be unserialized.
   */
  public function decode($raw) {
    $data = @unserialize($raw);
    return is_array($data) ? $data : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function write($name, array $data) {
    // For MongoDB the table need to exists. Otherwise MongoDB creates one
    // without the correct validation.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    $data = $this->encode($data);

    return $this->doWrite($name, $data);
  }

  /**
   * {@inheritdoc}
   */
  public function getAllCollectionNames() {
    $prefixed_table = $this->connection->getMongodbPrefixedTable($this->table);
    $collections = $this->connection->getConnection()->{$prefixed_table}->distinct(
      'collection',
      ['collection' => ['$ne' => StorageInterface::DEFAULT_COLLECTION]]
    );

    sort($collections);

    return $collections;
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
    // MongoDB does not remove duplicate values from the list.
    return array_unique(parent::listAll($prefix));
  }

}
