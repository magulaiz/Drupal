<?php

namespace Drupal\Core\Session;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Defines the refinable calculated permissions interface.
 */
interface RefinableCalculatedPermissionsInterface extends RefinableCacheableDependencyInterface, CalculatedPermissionsInterface {

  /**
   * Disables build mode.
   *
   * When build mode is on, which is the default state, only new items can be
   * added. Only after build mode is disabled can items be removed or replaced.
   *
   * @internal
   */
  public function disableBuildMode(): void;

  /**
   * Adds a calculated permission item.
   *
   * @param \Drupal\Core\Session\CalculatedPermissionsItemInterface $item
   *   The calculated permission item.
   * @param bool $overwrite
   *   (optional) Whether to overwrite an item if there already is one for the
   *   given identifier within the scope. Defaults to FALSE, meaning a merge
   *   will take place instead. Does nothing if build mode is still enabled.
   *
   * @return self
   */
  public function addItem(CalculatedPermissionsItemInterface $item, bool $overwrite = FALSE): self;

  /**
   * Removes a single calculated permission item from a given scope.
   *
   * Does nothing if build mode is still enabled.
   *
   * @param string $scope
   *   The scope name to remove the item from.
   * @param string|int $identifier
   *   The scope identifier to remove the item from.
   *
   * @return self
   */
  public function removeItem(string $scope, string|int $identifier): self;

  /**
   * Removes all of the calculated permission items, regardless of scope.
   *
   * Does nothing if build mode is still enabled.
   *
   * @return $this
   */
  public function removeItems(): self;

  /**
   * Removes all of the calculated permission items for the given scope.
   *
   * Does nothing if build mode is still enabled.
   *
   * @param string $scope
   *   The scope name to remove the items for.
   *
   * @return self
   */
  public function removeItemsByScope(string $scope): self;

  /**
   * Merge another calculated permissions object into this one.
   *
   * This merges (not replaces) all permissions and cacheable metadata.
   *
   * @param \Drupal\Core\Session\CalculatedPermissionsInterface $other
   *   The other calculated permissions object to merge into this one.
   *
   * @return self
   */
  public function merge(CalculatedPermissionsInterface $other): self;

}
