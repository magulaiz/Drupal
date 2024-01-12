<?php

namespace Drupal\layout_builder\Access;

use Drupal\Core\Session\AccountInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides an access check for the Layout Builder defaults.
 *
 * @ingroup layout_builder_access
 *
 * @internal
 *   Tagged services are internal.
 */
class LayoutBuilderUpdateBlockAccessCheck extends LayoutBuilderBlockAccessBase {

  /**
   * Checks routing access to the layout.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param string $delta
   *   The block delta.
   * @param string $uuid
   *   The block UUID.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(SectionStorageInterface $section_storage, string $delta, string $uuid, AccountInterface $account, Route $route) {
    $section_operation = $route->getRequirement('_layout_builder_update_block_access');

    // Case in tests leading to OutOfBoundsException('Invalid delta "0"').
    // @todo Investigate.
    try {
      $section = $section_storage->getSection($delta);
      $component = $section->getComponent($uuid);
      $plugin = $component->getPlugin();
    }
    catch (\OutOfBoundsException $e) {
      $plugin = NULL;
    }

    return $this->doCheckAccess($section_storage, $account, $section_operation, 'edit', $plugin);
  }

}
