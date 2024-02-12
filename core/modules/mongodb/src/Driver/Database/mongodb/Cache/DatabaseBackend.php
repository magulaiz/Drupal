<?php

namespace Drupal\mongodb\Driver\Database\mongodb\Cache;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\DatabaseBackend as CoreDatabaseBackend;
use Drupal\mongodb\Driver\Database\mongodb\Statement;
use MongoDB\BSON\Binary;
use MongoDB\BSON\Decimal128;

/**
 * The MongoDB implementation of \Drupal\Core\Cache\DatabaseBackend.
 */
class DatabaseBackend extends CoreDatabaseBackend {

  /**
   * Indicator for the existence of the database table.
   *
   * @var bool
   */
  protected $tableExists = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getMultiple(&$cids, $allow_invalid = FALSE) {
    $cid_mapping = [];
    foreach ($cids as $cid) {
      $cid_mapping[$this->normalizeCid($cid)] = $cid;
    }

    // When serving cached pages, the overhead of using ::select() was found
    // to add around 30% overhead to the request. Since $this->bin is a
    // variable, this means the call to ::query() here uses a concatenated
    // string. This is highly discouraged under any other circumstances, and
    // is used here only due to the performance overhead we would incur
    // otherwise. When serving an uncached page, the overhead of using
    // ::select() is a much smaller proportion of the request.
    $result = [];
    try {
      $prefixed_table = $this->connection->getMongodbPrefixedTable($this->bin);
      $cursor = $this->connection->getConnection()->{$prefixed_table}->find(
        ['cid' => ['$in' => array_keys($cid_mapping)]],
        [
          'projection' => ['cid' => 1, 'data' => 1, 'created' => 1, 'expire' => 1, 'serialized' => 1, 'tags' => 1, 'checksum' => 1, '_id' => 0],
          'sort' => ['cid' => 1],
        ]
      );

      $statement = new Statement($this->connection, $cursor, ['cid', 'data', 'created', 'expire', 'serialized', 'tags', 'checksum']);
      $result = $statement->execute()->fetchAll();
    }
    catch (\Exception $e) {
      // Nothing to do.
    }
    $cache = [];
    foreach ($result as $item) {
      // Map the cache ID back to the original.
      $item->cid = $cid_mapping[$item->cid];
      $item = $this->prepareItem($item, $allow_invalid);
      if ($item) {
        $cache[$item->cid] = $item;
      }
    }
    $cids = array_diff($cids, array_keys($cache));
    return $cache;
  }

  /**
   * {@inheritdoc}
   */
  protected function doSetMultiple(array $items) {
    $values = [];

    foreach ($items as $cid => $item) {
      $item += [
        'expire' => CacheBackendInterface::CACHE_PERMANENT,
        'tags' => [],
      ];

      assert(Inspector::assertAllStrings($item['tags']), 'Cache Tags must be strings.');
      $item['tags'] = array_unique($item['tags']);
      // Sort the cache tags so that they are stored consistently in the DB.
      sort($item['tags']);

      $fields = [
        'cid' => $this->normalizeCid($cid),
        'expire' => $item['expire'],
        'created' => new Decimal128(strval(round(microtime(TRUE), 3))),
        'tags' => implode(' ', $item['tags']),
        'checksum' => $this->checksumProvider->getCurrentChecksum($item['tags']),
      ];

      if (!is_string($item['data'])) {
        $fields['data'] = new Binary(serialize($item['data']), Binary::TYPE_GENERIC);
        $fields['serialized'] = TRUE;
      }
      else {
        $fields['data'] = new Binary($item['data'], Binary::TYPE_GENERIC);
        $fields['serialized'] = FALSE;
      }
      $values[] = $fields;
    }

    // Use an upsert query which is atomic and optimized for multiple-row
    // merges.
    $query = $this->connection
      ->upsert($this->bin)
      ->key('cid')
      ->fields(['cid', 'expire', 'created', 'tags', 'checksum', 'data', 'serialized']);
    foreach ($values as $fields) {
      // Only pass the values since the order of $fields matches the order of
      // the insert fields. This is a performance optimization to avoid
      // unnecessary loops within the method.
      $query->values(array_values($fields));
    }

    $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $items) {
    // For MongoDB the table need to exists. Otherwise MongoDB creates one
    // without the correct validation.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureBinExists();
    }

    $this->doSetMultiple($items);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $cids) {
    // For MongoDB the table need to exists. Otherwise MongoDB creates one
    // without the correct validation.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureBinExists();
    }

    $cids = array_values(array_map([$this, 'normalizeCid'], $cids));

    // Delete in chunks when a large array is passed.
    foreach (array_chunk($cids, 1000) as $cids_chunk) {
      $this->connection->delete($this->bin)
        ->condition('cid', $cids_chunk, 'IN')
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    // For MongoDB the table need to exists. Otherwise MongoDB creates one
    // without the correct validation.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureBinExists();
    }

    $this->connection->truncate($this->bin)->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
    try {
      // Bounded size cache bin, using FIFO.
      if ($this->maxRows !== static::MAXIMUM_NONE) {
        $first_invalid_create_time = $this->connection->select($this->bin)
          ->fields($this->bin, ['created'])
          // The orderBY field does not need to add the table name.
          ->orderBy('created', 'DESC')
          ->range($this->maxRows, $this->maxRows + 1)
          ->execute()
          ->fetchField();

        if ($first_invalid_create_time) {
          // The created field is saved in MongoDB as Decimal128.
          $first_invalid_create_time = new Decimal128($first_invalid_create_time);
          $this->connection->delete($this->bin)
            ->condition('created', $first_invalid_create_time, '<=')
            ->execute();
        }
      }

      $this->connection->delete($this->bin)
        ->condition('expire', Cache::PERMANENT, '<>')
        ->condition('expire', REQUEST_TIME, '<')
        ->execute();
    }
    catch (\Exception $e) {
      // If the table does not exist, it surely does not have garbage in it.
      // If the table exists, the next garbage collection will clean up.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function schemaDefinition() {
    $schema = parent::schemaDefinition();
    // The date field cannot be transformed to a real date field, because it can
    // be set to infinity with the value -1.
    $schema['fields']['serialized'] = [
      'description' => 'A flag to indicate whether content is serialized (TRUE) or not (FALSE).',
      'type' => 'bool',
      'not null' => TRUE,
      'default' => FALSE,
    ];
    return $schema;
  }

}
