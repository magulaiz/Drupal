<?php

namespace Drupal\mongodb\Lock;

use Drupal\Core\Lock\DatabaseLockBackend as CoreDatabaseLockBackend;
use Drupal\mongodb\Driver\Database\mongodb\Statement;
use MongoDB\Driver\Exception\BulkWriteException;

/**
 * The MongoDB implementation of \Drupal\Core\Lock\DatabaseLockBackend.
 */
class DatabaseLockBackend extends CoreDatabaseLockBackend {

  /**
   * {@inheritdoc}
   */
  public function lockMayBeAvailable($name) {
    $name = $this->normalizeName($name);

    try {
      $prefixed_table = $this->database->getPrefix() . 'semaphore';
      $cursor = $this->database->getConnection()->selectCollection($prefixed_table)->find(
        ['name' => ['$eq' => $name]],
        [
          'projection' => ['expire' => 1, 'value' => 1, '_id' => 0],
          'session' => $this->database->getMongodbSession(),
        ]
      );

      $statement = new Statement($this->database, $cursor, ['expire', 'value']);
      $lock = $statement->execute()->fetchAssoc();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      // If the table does not exist yet then the lock may be available.
      $lock = FALSE;
    }
    if (!$lock) {
      return TRUE;
    }
    $expire = (float) $lock['expire'];
    $now = microtime(TRUE);
    if ($now > $expire) {
      // We check two conditions to prevent a race condition where another
      // request acquired the lock and set a new expire time. We add a small
      // number to $expire to avoid errors with float to string conversion.
      return (bool) $this->database->delete('semaphore')
        ->condition('name', $name)
        ->condition('value', $lock['value'])
        ->condition('expire', 0.0001 + $expire, '<=')
        ->execute();
    }
    return FALSE;
  }

}
