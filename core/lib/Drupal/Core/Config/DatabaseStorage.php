<?php

namespace Drupal\Core\Config;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\SchemaProviderInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Defines the Database storage.
 */
class DatabaseStorage implements StorageInterface, SchemaProviderInterface {
  use DependencySerializationTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The database table name.
   *
   * @var string
   */
  protected $table;

  /**
   * Additional database connection options to use in queries.
   *
   * @var array
   */
  protected $options = [];

  /**
   * The storage collection.
   *
   * @var string
   */
  protected $collection = StorageInterface::DEFAULT_COLLECTION;

  /**
   * Constructs a new DatabaseStorage.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   A Database connection to use for reading and writing configuration data.
   * @param string $table
   *   A database table name to store configuration data in.
   * @param array $options
   *   (optional) Any additional database connection options to use in queries.
   * @param string $collection
   *   (optional) The collection to store configuration in. Defaults to the
   *   default collection.
   */
  public function __construct(Connection $connection, $table, array $options = [], $collection = StorageInterface::DEFAULT_COLLECTION) {
    $this->connection = $connection;
    $this->table = $table;
    $this->options = $options + ['schema_provider' => $this];
    $this->collection = $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    $options = [
      'create_missing_table' => FALSE,
    ] + $this->options;
    try {
      return (bool) $this->connection->queryRange('SELECT 1 FROM {' . $this->connection->escapeTable($this->table) . '} WHERE [collection] = :collection AND [name] = :name', 0, 1, [
        ':collection' => $this->collection,
        ':name' => $name,
      ], $options)->fetchField();
    }
    catch (\Exception $e) {
      // If we attempt a read without actually having the database or the table
      // available, just return FALSE so the caller can handle it.
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function read($name) {

    $raw = $this->connection->query('SELECT [data] FROM {' . $this->connection->escapeTable($this->table) . '} WHERE [collection] = :collection AND [name] = :name', [':collection' => $this->collection, ':name' => $name], $this->options)->fetchField();
    return $raw !== FALSE ? $this->decode($raw) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function readMultiple(array $names) {
    $list = [];
    try {
      $list = $this->connection->query('SELECT [name], [data] FROM {' . $this->connection->escapeTable($this->table) . '} WHERE [collection] = :collection AND [name] IN ( :names[] )', [':collection' => $this->collection, ':names[]' => $names], $this->options)->fetchAllKeyed();
      foreach ($list as &$data) {
        $data = $this->decode($data);
      }
    }
    catch (\Exception $e) {
      // If we attempt a read without actually having the database or the table
      // available, just return an empty array so the caller can handle it.
    }
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function write($name, array $data) {
    $data = $this->encode($data);
    // @todo Remove the 'return' option in Drupal 11.
    // @see https://www.drupal.org/project/drupal/issues/3256524
    $options = ['return' => Database::RETURN_AFFECTED] + $this->options;
    return (bool) $this->connection->merge($this->table, $options)
      ->keys(['collection', 'name'], [$this->collection, $name])
      ->fields(['data' => $data])
      ->execute();
  }

  /**
   * Defines the schema for the configuration table.
   *
   * @internal
   */
  public function getSchema($table_name) {
    $schema = [
      'description' => 'The base table for configuration data.',
      'fields' => [
        'collection' => [
          'description' => 'Primary Key: Config object collection.',
          'type' => 'varchar_ascii',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ],
        'name' => [
          'description' => 'Primary Key: Config object name.',
          'type' => 'varchar_ascii',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ],
        'data' => [
          'description' => 'A serialized configuration object data.',
          'type' => 'blob',
          'not null' => FALSE,
          'size' => 'big',
        ],
      ],
      'primary key' => ['collection', 'name'],
    ];
    return $this->table == $table_name ? $schema : FALSE;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::delete().
   *
   * @throws \PDOException
   *
   * @todo Ignore replica targets for data manipulation operations.
   */
  public function delete($name) {
    // @todo Remove the 'return' option in Drupal 11.
    // @see https://www.drupal.org/project/drupal/issues/3256524
    $options = [
      'return' => Database::RETURN_AFFECTED,
      'create_missing_table' => FALSE,
    ] + $this->options;
    return (bool) $this->connection->delete($this->table, $options)
      ->condition('collection', $this->collection)
      ->condition('name', $name)
      ->execute();
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::rename().
   *
   * @throws \PDOException
   */
  public function rename($name, $new_name) {
    // @todo Remove the 'return' option in Drupal 11.
    // @see https://www.drupal.org/project/drupal/issues/3256524
    $options = ['return' => Database::RETURN_AFFECTED] + $this->options;
    return (bool) $this->connection->update($this->table, $options)
      ->fields(['name' => $new_name])
      ->condition('name', $name)
      ->condition('collection', $this->collection)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data) {
    return serialize($data);
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::decode().
   *
   * @throws \ErrorException
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
  public function listAll($prefix = '') {
    try {
      $query = $this->connection->select($this->table, NULL, $this->options);
      $query->fields($this->table, ['name']);
      $query->condition('collection', $this->collection, '=');
      $query->condition('name', $prefix . '%', 'LIKE');
      $query->orderBy('collection')->orderBy('name');
      return $query->execute()->fetchCol();
    }
    catch (\Exception $e) {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll($prefix = '') {
    try {
      // @todo Remove the 'return' option in Drupal 11.
      // @see https://www.drupal.org/project/drupal/issues/3256524
      $options = [
        'return' => Database::RETURN_AFFECTED,
        'create_missing_table' => FALSE,
      ] + $this->options;
      return (bool) $this->connection->delete($this->table, $options)
        ->condition('name', $prefix . '%', 'LIKE')
        ->condition('collection', $this->collection)
        ->execute();
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createCollection($collection) {
    return new static(
      $this->connection,
      $this->table,
      $this->options,
      $collection
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionName() {
    return $this->collection;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllCollectionNames() {
    try {
      return $this->connection->query('SELECT DISTINCT [collection] FROM {' . $this->connection->escapeTable($this->table) . '} WHERE [collection] <> :collection ORDER by [collection]', [
        ':collection' => StorageInterface::DEFAULT_COLLECTION,
      ], $this->options)->fetchCol();
    }
    catch (\Exception $e) {
      return [];
    }
  }

}
