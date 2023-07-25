<?php

namespace Drupal\Core\Session;

use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Defines the calculated permissions interface.
 */
interface CalculatedPermissionsInterface extends CacheableDependencyInterface {

  /**
   * Retrieves a single calculated permission item from a given scope.
   *
   * @param string $scope
   *   The scope name to retrieve the item for.
   * @param string|int $identifier
   *   The scope identifier to retrieve the item for.
   *
   * @return \Drupal\Core\Session\CalculatedPermissionsItemInterface|false
   *   The calculated permission item or FALSE if it could not be found.
   */
  public function getItem(string $scope, string|int $identifier): CalculatedPermissionsItemInterface|false;

  /**
   * Retrieves all of the calculated permission items, regardless of scope.
   *
   * @return \Drupal\Core\Session\CalculatedPermissionsItemInterface[]
   *   A list of calculated permission items.
   */
  public function getItems(): array;

  /**
   * Retrieves all of the scopes that have items for them.
   *
   * @return string[]
   *   The scope names that are in use.
   */
  public function getScopes(): array;

  /**
   * Retrieves all of the calculated permission items for the given scope.
   *
   * @param string $scope
   *   The scope name to retrieve the items for.
   *
   * @return \Drupal\Core\Session\CalculatedPermissionsItemInterface[]
   *   A list of calculated permission items for the given scope.
   */
  public function getItemsByScope(string $scope): array;

}
