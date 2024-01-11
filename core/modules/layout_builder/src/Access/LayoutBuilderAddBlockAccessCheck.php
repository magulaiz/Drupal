<?php

namespace Drupal\layout_builder\Access;

use Drupal\Core\Block\BlockManagerInterface;
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
class LayoutBuilderAddBlockAccessCheck extends LayoutBuilderBlockAccessBase {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $blockManager
   *   The block plugin manager.
   */
  public function __construct(
    protected readonly BlockManagerInterface $blockManager
  ) {}

  /**
   * Checks routing access to the layout.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param string $plugin_id
   *   The block plugin ID.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(SectionStorageInterface $section_storage, string $plugin_id, AccountInterface $account, Route $route) {
    $plugin = $this->blockManager->createInstance($plugin_id, []);
    return $this->doCheckAccess($section_storage, $account, 'create', $plugin);
  }

}
