<?php

declare(strict_types=1);

namespace Drupal\block;

/**
 * Provides a BC layer for modules providing old configurations.
 *
 * @internal
 */
class BlockConfigUpdater {

  /**
   * Flag determining whether deprecations should be triggered.
   *
   * @var bool
   */
  protected bool $deprecationsEnabled = TRUE;

  /**
   * Stores which deprecations were triggered.
   *
   * @var array
   */
  protected array $triggeredDeprecations = [];

  /**
   * Sets the deprecations enabling status.
   *
   * @param bool $enabled
   *   Whether deprecations should be enabled.
   */
  public function setDeprecationsEnabled(bool $enabled): void {
    $this->deprecationsEnabled = $enabled;
  }

  /**
   * Performs the required update.
   *
   * @param \Drupal\block\BlockInterface $block
   *   The block to update.
   *
   * @return bool
   *   Whether the block was updated.
   */
  public function updateBlock(BlockInterface $block): bool {
    $changed = FALSE;
    if ($this->needsConditionalLogicSettingsUpdate($block)) {
      $settings = $block->get('settings');
      $settings['condition_logic'] = 'and';
      $block->set('settings', $settings);
      $changed = TRUE;
    }
    return $changed;
  }

  /**
   * Updates the conditional logic settings based on current settings.
   *
   * @param \Drupal\block\BlockInterface $block
   *   The block to update.
   *
   * @return bool
   *   TRUE if the block has been updated.
   */
  public function needsConditionalLogicSettingsUpdate(BlockInterface $block): bool {
    $settings = $block->get('settings');
    if (\array_key_exists('condition_logic', $settings) === FALSE && $block->uuid() !== NULL) {
      return TRUE;
    }
    return FALSE;
  }

}
