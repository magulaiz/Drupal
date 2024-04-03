<?php

namespace Drupal\mongodb\Batch;

use Drupal\Core\Batch\BatchStorage as CoreBatchStorage;
use MongoDB\BSON\UTCDateTime;

/**
 * The MongoDB implementation of \Drupal\Core\Batch\BatchStorage.
 */
class BatchStorage extends CoreBatchStorage {

  /**
   * Indicator for the existence of the database table.
   *
   * @var bool
   */
  protected $tableExists = FALSE;

  /**
   * Returns a new batch id.
   *
   * @return int
   *   A batch id.
   */
  public function getId(): int {
    // For MongoDB the table needs to exist. Otherwise MongoDB creates one
    // without the correct validation.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    return $this->connection->insert(static::TABLE_NAME)
      ->fields([
        'timestamp' => new UTCDateTime($this->time->getRequestTime() * 1000),
        'token' => '',
        'batch' => NULL,
      ])
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    // Ensure that a session is started before using the CSRF token generator.
    $this->session->start();
    try {
      $batch = $this->connection->select(static::TABLE_NAME, 'b')
        ->fields('b', ['batch'])
        ->condition('bid', (int) $id)
        ->condition('token', $this->csrfToken->get($id))
        ->execute()
        ->fetchField();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      $batch = FALSE;
    }
    if ($batch) {
      return unserialize($batch);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($id) {
    $id = (int) $id;

    parent::delete($id);
  }

  /**
   * {@inheritdoc}
   */
  public function update(array $batch) {
    // For MongoDB the table needs to exist. Otherwise MongoDB creates one
    // without the correct validation.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    if (isset($batch['id'])) {
      $batch['id'] = (int) $batch['id'];
    }

    parent::update($batch);
  }

  /**
   * {@inheritdoc}
   */
  public function cleanup() {
    try {
      $timestamp = new UTCDateTime(($this->time->getRequestTime() - 864000) * 1000);

      // Cleanup the batch table and the queue for failed batches.
      $this->connection->delete('batch')
        ->condition('timestamp', $timestamp, '<')
        ->execute();
    }
    catch (\Exception $e) {
      $this->catchException($e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $batch) {
    // For MongoDB the table need to exists. Otherwise MongoDB creates one
    // without the correct validation.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    // For MongoDB an integer value should be a real integer.
    $batch['id'] = (int) $batch['id'];

    parent::create($batch);
  }

  /**
   * {@inheritdoc}
   */
  public function schemaDefinition() {
    $schema = parent::schemaDefinition();

    // For MongoDB timestamps are stored as real dates.
    $schema['fields']['timestamp']['type'] = 'date';

    return $schema;
  }

}
