<?php

namespace Drupal\layout_builder_tempstore_access_test\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;

use Drupal\layout_builder\SectionStorageInterface;

/**
 * Only allow access if the supplied section storage is not empty.
 */
class SectionStorageEmptyAccessCheck implements AccessInterface {

  /**
   * Only allow access if the supplied section storage is not empty.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(SectionStorageInterface $section_storage) {
    if ($section_storage->count() > 0) {
      return AccessResult::allowed()->setCacheMaxAge(0);
    }

    return AccessResult::forbidden()->setCacheMaxAge(0);
  }

}
