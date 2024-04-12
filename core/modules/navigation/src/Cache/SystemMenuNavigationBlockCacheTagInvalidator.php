<?php

declare(strict_types=1);

namespace Drupal\navigation\Cache;

use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\navigation\NavigationBlockManagerInterface;

/**
 * Invalidates navigation block cache when menus are updated.
 */
class SystemMenuNavigationBlockCacheTagInvalidator implements CacheTagsInvalidatorInterface {

  /**
   * Constructs a new SystemMenuNavigationBlockCacheTagInvalidator.
   *
   * @param \Drupal\navigation\NavigationBlockManagerInterface $navigationBlockManager
   *   The navigation block manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(protected NavigationBlockManagerInterface $navigationBlockManager, protected EntityTypeManagerInterface $entityTypeManager) {
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateTags(array $tags) {
    // Ensure that definitions cache is cleared when menus are updated.
    try {
      $menu_tags = $this->entityTypeManager->getDefinition('menu')
        ->getListCacheTags();
    }
    catch (PluginNotFoundException $e) {
      return;
    }
    if (!empty(array_intersect($menu_tags, $tags))) {
      return;
    }

    if ($this->navigationBlockManager instanceof CachedDiscoveryInterface) {
      $this->navigationBlockManager->clearCachedDefinitions();
    }
  }

}
