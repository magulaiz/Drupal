<?php

namespace Drupal\layout_builder\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layout_builder\Plugin\Block\InlineBlock;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides an access check for the Layout Builder create block route.
 *
 * @ingroup layout_builder_access
 *
 * @internal
 *   Tagged services are internal.
 */
class LayoutBuilderAddBlockAccessCheck implements AccessInterface {

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
    $operation = $route->getRequirement('_layout_builder_access');

    $access = $section_storage->access($operation, $account, TRUE);
    if ($plugin instanceof InlineBlock) {
      $access = $access->andIf($plugin->blockAddAccess($account, $section_storage, $operation));
    }

    // Check for the global permission unless the section storage checks
    // permissions itself.
    if (!$section_storage->getPluginDefinition()->get('handles_permission_check')) {
      $access = $access->andIf(AccessResult::allowedIfHasPermission($account, 'configure any layout'));
    }

    if ($access instanceof RefinableCacheableDependencyInterface) {
      $access->addCacheableDependency($section_storage);
    }
    return $access;
  }

}
