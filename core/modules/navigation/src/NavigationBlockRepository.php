<?php

namespace Drupal\navigation;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;

/**
 * Provides a repository for Navigation block config entities.
 */
class NavigationBlockRepository implements NavigationBlockRepositoryInterface {

  /**
   * The navigation_block storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $navigationBlockStorage;

  /**
   * The context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * Constructs a new NavigationBlockRepository.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The plugin context handler.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ContextHandlerInterface $context_handler) {
    $this->navigationBlockStorage = $entity_type_manager->getStorage('navigation_block');
    $this->contextHandler = $context_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getVisibleNavigationBlocksPerRegion(array &$cacheable_metadata = []) {
    // Build an array of the region names in the right order.
    $empty = array_fill_keys([self::REGION_CONTENT, self::REGION_FOOTER], []);

    $full = [];
    foreach ($this->navigationBlockStorage->loadMultiple() as $navigation_block_id => $navigation_block) {
      /** @var \Drupal\navigation\NavigationBlockInterface $navigation_block */
      $access = $navigation_block->access('view', NULL, TRUE);
      $region = $navigation_block->getRegion();
      if (!isset($cacheable_metadata[$region])) {
        $cacheable_metadata[$region] = CacheableMetadata::createFromObject($access);
      }
      else {
        $cacheable_metadata[$region] = $cacheable_metadata[$region]->merge(CacheableMetadata::createFromObject($access));
      }

      // Set the contexts on the navigation_block before checking access.
      if ($access->isAllowed()) {
        $full[$region][$navigation_block_id] = $navigation_block;
      }
    }

    // Merge it with the actual values to maintain the region ordering.
    $assignments = array_intersect_key(array_merge($empty, $full), $empty);
    foreach ($assignments as &$assignment) {
      uasort($assignment, 'Drupal\navigation\Entity\NavigationBlock::sort');
    }
    return $assignments;
  }

  /**
   * {@inheritdoc}
   */
  public function getUniqueMachineName(string $suggestion): string {
    // Get all the block machine names that begin with the suggested string.
    $query = $this->navigationBlockStorage->getQuery();
    $query->accessCheck(FALSE);
    $query->condition('id', $suggestion, 'CONTAINS');
    $navigation_block_ids = $query->execute();

    $navigation_block_ids = array_map(function ($navigation_block_id) {
      $parts = explode('.', $navigation_block_id);
      return end($parts);
    }, $navigation_block_ids);

    // Iterate through potential IDs until we get a new one. E.g.
    // For example, 'plugin', 'plugin_2', 'plugin_3', etc.
    $count = 1;
    $machine_default = $suggestion;
    while (in_array($machine_default, $navigation_block_ids)) {
      $machine_default = $suggestion . '_' . ++$count;
    }
    return $machine_default;
  }

}
