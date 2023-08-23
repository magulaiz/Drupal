<?php

namespace Drupal\Tests;

use Drupal\Component\FileSystem\FileSystem;

/**
 * Trait for creating a test ID from which derives the site path and DB prefix.
 */
trait SitePrefixTrait {

  /**
   * Undocumented function
   *
   * @param boolean $create_lock
   *   (optional) Whether or not to create a lock file. Defaults to FALSE. If
   *   the environment variable RUN_TESTS_CONCURRENCY is greater than 1 it will
   *   be overridden to TRUE regardless of its initial value.
   *
   * @return int
   *   The unique lock ID for the test method.
   */
  // KILL
  protected function createLockId(bool $create_lock = FALSE): int {
    if (!isset($this->lockId)) {
      $this->lockId = $this->getTestLock($create_lock);
    }

    return $this->lockId;
  }

  /**
   * Generates the test database prefix and sets it as a property.
   */
  protected function prepareDatabasePrefix() {
    if (!isset($this->databasePrefix)) {
      $this->databasePrefix = 'test' . $this->lockId;
    }
  }

  /**
   * Gets the relative path to the test site directory.
   *
   * @return string
   *   The relative path to the test site directory.
   */
  public function getTestSitePath(): string {
    return 'sites/simpletest/' . $this->lockId;
  }

  /**
   * Generates a unique lock ID for the test method.
   *
   * @param bool $create_lock
   *   (optional) Whether or not to create a lock file. Defaults to FALSE.
   *
   * @return int
   *   The unique lock ID for the test method.
   */
  protected function createTestLock(bool $create_lock = FALSE): int {
    // There is a risk that the generated random number is a duplicate. This
    // would cause different tests to try to use the same database prefix.
    // Therefore, if running with a concurrency of greater than 1, we need to
    // create a lock.
    if (getenv('RUN_TESTS_CONCURRENCY') > 1) {
      $create_lock = TRUE;
    }

    do {
      $lock_id = mt_rand(10000000, 99999999);
      if ($create_lock && @symlink(__FILE__, $this->getLockFile($lock_id)) === FALSE) {
        // If we can't create a symlink, the lock ID is in use. Generate another
        // one. Symlinks are used because they are atomic and reliable.
        $lock_id = NULL;
      }
    } while ($lock_id === NULL);
    return $lock_id;
  }

  /**
   * Releases a lock.
   *
   * @return bool
   *   TRUE if successful, FALSE if not.
   */
  public function releaseLock(): bool {
    return unlink($this->getLockFile($this->lockId));
  }

  /**
   * Releases all test locks.
   *
   * This should only be called once all the test fixtures have been cleaned up.
   */
  public static function releaseAllTestLocks(): void {
    $tmp = FileSystem::getOsTemporaryDirectory();
    $dir = dir($tmp);
    while (($entry = $dir->read()) !== FALSE) {
      if ($entry === '.' || $entry === '..') {
        continue;
      }
      $entry_path = $tmp . '/' . $entry;
      if (preg_match('/^test_\d+/', $entry) && is_link($entry_path)) {
        unlink($entry_path);
      }
    }
  }

  /**
   * Gets the lock file path.
   *
   * @param int $lock_id
   *   The test method lock ID.
   *
   * @return string
   *   A file path to the symbolic link that prevents the lock ID being re-used.
   */
  protected function getLockFile(int $lock_id): string {
    return FileSystem::getOsTemporaryDirectory() . '/test_' . $lock_id;
  }

  /**
   * Gets the file path of the PHP error log of the test.
   *
   * @return string
   *   The relative path to the test site PHP error log file.
   */
  public function getPhpErrorLogPath(): string {
    return $this->getTestSitePath() . '/error.log';
  }

}
