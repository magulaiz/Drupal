<?php

namespace Drupal\file;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines an interface for file entity storage classes.
 *
 * @method \Drupal\file\FileInterface create(array $values = [])
 * @method null|\Drupal\file\FileInterface load($id)
 * @method null|\Drupal\file\FileInterface loadRevision($revision_id)
 * @method null|\Drupal\file\FileInterface loadUnchanged($id)
 * @method \Drupal\file\FileInterface[] loadMultiple(array $ids = NULL)
 * @method \Drupal\file\FileInterface[] loadByProperties(array $values = [])
 * @method null|int save(\Drupal\file\FileInterface $entity)
 * @method void restore(\Drupal\file\FileInterface $entity)
 */
interface FileStorageInterface extends ContentEntityStorageInterface {

  /**
   * Determines total disk space used by a single user or the whole filesystem.
   *
   * @param int $uid
   *   Optional. A user id, specifying NULL returns the total space used by all
   *   non-temporary files.
   * @param int $status
   *   (Optional) The file status to consider. The default is to only
   *   consider files in status FileInterface::STATUS_PERMANENT.
   *
   * @return int
   *   An integer containing the number of bytes used.
   */
  public function spaceUsed($uid = NULL, $status = FileInterface::STATUS_PERMANENT);

}
