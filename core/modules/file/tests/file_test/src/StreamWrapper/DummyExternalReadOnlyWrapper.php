<?php

namespace Drupal\file_test\StreamWrapper;

use Drupal\Core\StreamWrapper\ReadOnlyStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;

/**
 * Helper class for testing the stream wrapper registry.
 *
 * Dummy external stream wrapper implementation (dummy-external-readonly://).
 */
class DummyExternalReadOnlyWrapper extends ReadOnlyStream {

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public static function getType() {
    return StreamWrapperInterface::READ_VISIBLE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function getName() {
    return t('Dummy external stream wrapper (readonly)');
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function getDescription() {
    return t('Dummy external read-only stream wrapper for testing.');
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function getExternalUrl() {
    [, $target] = explode('://', $this->uri, 2);
    return 'https://www.dummy-external-readonly.com/' . $target;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function realpath() {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function dirname($uri = NULL) {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function dir_closedir() {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function dir_opendir($path, $options) {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function dir_readdir() {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function dir_rewinddir() {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function stream_cast($cast_as) {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function stream_close() {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function stream_eof() {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function stream_read($count) {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function stream_seek($offset, $whence = SEEK_SET) {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function stream_set_option($option, $arg1, $arg2) {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function stream_stat() {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function stream_tell() {
    return FALSE;
  }

  /**
<<<<<<< HEAD
   * @inheritDoc
=======
   * {@inheritdoc}
>>>>>>> upstream/11.x
   */
  public function url_stat($path, $flags) {
    return FALSE;
  }

}
