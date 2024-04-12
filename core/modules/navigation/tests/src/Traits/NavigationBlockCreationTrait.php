<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Traits;

use Drupal\navigation\Entity\NavigationBlock;
use Drupal\navigation\NavigationBlockRepositoryInterface;

/**
 * Provides methods to create and place navigation block with default settings.
 *
 * This trait is meant to be used only by test classes.
 */
trait NavigationBlockCreationTrait {

  /**
   * Creates a navigation block instance based on default settings.
   *
   * @param string $plugin_id
   *   The plugin ID of the navigation block type for this navigation block
   *   instance.
   * @param array $settings
   *   (optional) An associative array of settings for the navigation block
   *   entity. Override the defaults by specifying the key and value in the
   *   array, for example:
   *   @code
   *     $this->drupalPlaceNavigationBlock('user', [
   *       'label' => t('Hello, world!'),
   *     ]);
   *   @endcode
   *   The following defaults are provided:
   *   - label: Random string.
   *   - id: Random string.
   *   - region: 'content'.
   *   - visibility: Empty array.
   *
   * @return \Drupal\navigation\NavigationBlockInterface
   *   The navigation block entity.
   */
  protected function drupalPlaceNavigationBlock($plugin_id, array $settings = []) {
    $settings += [
      'plugin' => $plugin_id,
      'region' => NavigationBlockRepositoryInterface::REGION_CONTENT,
      'id' => $this->randomMachineName(8),
      'label' => $this->randomMachineName(8),
      'visibility' => [],
      'weight' => 0,
    ];
    $values = [];
    foreach (['region', 'id', 'plugin', 'weight', 'visibility'] as $key) {
      $values[$key] = $settings[$key];
      // Remove extra values that do not belong in the settings array.
      unset($settings[$key]);
    }
    foreach ($values['visibility'] as $id => $visibility) {
      $values['visibility'][$id]['id'] = $id;
    }
    $values['settings'] = $settings;
    $navigation_block = NavigationBlock::create($values);
    $navigation_block->save();
    return $navigation_block;
  }

}
