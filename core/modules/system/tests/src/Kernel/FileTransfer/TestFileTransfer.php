<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Kernel\FileTransfer;

use Drupal\Core\FileTransfer\ChmodInterface;
use Drupal\Core\FileTransfer\FileTransfer;

/**
 * Mock FileTransfer object for test case.
 */
class TestFileTransfer extends FileTransfer implements ChmodInterface {

  /**
   * {@inheritdoc}
   */
  protected $hostname = '';

  /**
   * {@inheritdoc}
   */
  protected $username = '';

  /**
   * {@inheritdoc}
   */
  protected $password = '';

  /**
   * {@inheritdoc}
   */
  protected $port = 0;

  /**
   * The connection.
   */
  protected MockTestConnection $connection;

  /**
   * This is for testing the CopyRecursive logic.
   *
   * @var bool
   */
  public bool $shouldIsDirectoryReturnTrue = FALSE;

  /**
   * {@inheritdoc}
   */
  public static function factory($jail, $settings) {
    assert(is_array($settings));
    return new TestFileTransfer($jail);
  }

  /**
   * {@inheritdoc}
   */
  public function connect() {
    $this->connection = new MockTestConnection();
    // Access the connection via the property. The property used to be set via a
    // magic method and this can cause problems if coded incorrectly.
    $this->connection->connectionString = 'test://' . urlencode($this->username) . ':' . urlencode($this->password) . "@$this->host:$this->port/";

  }

  /**
   * {@inheritdoc}
   */
  protected function copyFileJailed($source, $destination) {
    $this->connection->run("copyFile $source $destination");
  }

  /**
   * {@inheritdoc}
   */
  protected function removeDirectoryJailed($directory) {
    $this->connection->run("rmdir $directory");
  }

  /**
   * {@inheritdoc}
   */
  protected function createDirectoryJailed($directory) {
    $this->connection->run("mkdir $directory");
  }

  /**
   * {@inheritdoc}
   */
  protected function removeFileJailed($destination) {
    $this->connection->run("rm $destination");
  }

  /**
   * {@inheritdoc}
   */
  public function isDirectory($path) {
    return $this->shouldIsDirectoryReturnTrue;
  }

  /**
   * {@inheritdoc}
   */
  public function isFile($path) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function chmodJailed($path, $mode, $recursive) {}

}
