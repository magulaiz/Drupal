<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\file\FileInterface;
use Drupal\file\FileStorageInterface;

/**
 * The MongoDB implementation of \Drupal\file\FileStorage.
 */
class FileStorage extends ContentEntityStorage implements FileStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function spaceUsed($uid = NULL, $status = FileInterface::STATUS_PERMANENT) {
    $query = $this->database->select($this->entityType->getBaseTable(), 'f')
      ->condition('status', (bool) $status);
    if (isset($uid)) {
      $query->condition('uid', (int) $uid);
    }
    $files = $query->execute()->fetchAll();

    $size = 0;
    foreach ($files as $file) {
      if (isset($file->filesize)) {
        $size += $file->filesize;
      }
    }

    return $size;
  }

}
