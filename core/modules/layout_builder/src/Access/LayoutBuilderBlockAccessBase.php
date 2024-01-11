<?php

declare(strict_types=1);

namespace Drupal\layout_builder\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layout_builder\Plugin\Block\InlineBlock;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Base class for layout builder block access checks.
 */
abstract class LayoutBuilderBlockAccessBase implements AccessInterface {

  /**
   * The actual access checker that uses the block plugin.
   */
  protected function doCheckAccess(
    SectionStorageInterface $section_storage,
    AccountInterface $account,
    string $operation,
    BlockPluginInterface $plugin
  ) {
    if ($plugin instanceof InlineBlock) {
      $access = $plugin->editAccess($account, $section_storage, $operation);
    }
    else {
      $access = $section_storage->access($operation, $account, TRUE);
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
