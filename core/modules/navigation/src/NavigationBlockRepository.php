<?php

namespace Drupal\navigation;

use Drupal\block\BlockRepository;

/**
 * Provides a repository for Navigation block config entities.
 */
class NavigationBlockRepository extends BlockRepository implements NavigationBlockRepositoryInterface {

  protected function getRegionsList(): array {
    return [self::REGION_CONTENT, self::REGION_FOOTER];
  }

  protected function buildBlockPropertyQuery(): array {
    return ['theme' => ''];
  }

}
