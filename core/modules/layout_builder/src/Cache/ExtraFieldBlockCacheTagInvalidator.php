<?php

namespace Drupal\layout_builder\Cache;

use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a cache tag invalidator that clears the block cache.
 *
 * @internal
 *   Tagged services are internal.
 */
class ExtraFieldBlockCacheTagInvalidator implements CacheTagsInvalidatorInterface, ContainerInjectionInterface {

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * ExtraFieldBlockCacheTagInvalidator constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   */
  public function __construct(BlockManagerInterface $block_manager) {
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateTags(array $tags) {
    if (in_array('entity_field_info', $tags, TRUE)) {
      if ($this->blockManager instanceof CachedDiscoveryInterface) {
        $this->blockManager->clearCachedDefinitions();
      }
    }
  }

}
