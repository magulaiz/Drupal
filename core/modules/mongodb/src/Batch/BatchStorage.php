<?php

namespace Drupal\mongodb\Batch;

use Drupal\Core\Batch\BatchStorage as CoreBatchStorage;
use Drupal\mongodb\Driver\Database\mongodb\Statement;

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
    // For MongoDB the table need to exists. Otherwise MongoDB creates one
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
  public function create(array $batch) {
    // For MongoDB the table need to exists. Otherwise MongoDB creates one
    // without the correct validation.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    // Ensure that a session is started before using the CSRF token generator.
    $this->session->start();
    $this->doCreate($batch);
  }

}
