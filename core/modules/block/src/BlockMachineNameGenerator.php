<?php

namespace Drupal\block;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a class which generates unique machine block names.
 */
class BlockMachineNameGenerator implements BlockMachineNameGeneratorInterface {

  /**
   * Block storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $blockStorage;

  /**
   * Constructs a new BlockMachineNameGenerator.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->blockStorage = $entityTypeManager->getStorage('block');
  }

  /**
   * {@inheritdoc}
   */
  public function getUniqueMachineName(string $suggestion, string $theme = NULL): string {
    if ($theme) {
      $suggestion = $theme . '_' . $suggestion;
    }
    // Get all the block machine names that begin with the suggested string.
    $query = $this->blockStorage->getQuery();
    $query->accessCheck(FALSE);
    $query->condition('id', $suggestion, 'CONTAINS');
    $block_ids = $query->execute();

    $block_ids = array_map(function ($block_id) {
      $parts = explode('.', $block_id);
      return end($parts);
    }, $block_ids);

    // Iterate through potential IDs until we get a new one. E.g.
    // For example, 'plugin', 'plugin_2', 'plugin_3', etc.
    $count = 1;
    $machine_default = $suggestion;
    while (in_array($machine_default, $block_ids)) {
      $machine_default = $suggestion . '_' . ++$count;
    }
    return $machine_default;
  }

}
