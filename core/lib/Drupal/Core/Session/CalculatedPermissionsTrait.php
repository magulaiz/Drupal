<?php

namespace Drupal\Core\Session;

/**
 * Trait for \Drupal\Core\Session\CalculatedPermissionsInterface.
 */
trait CalculatedPermissionsTrait {

  /**
   * A list of calculated permission items, keyed by scope and identifier.
   *
   * @var array
   */
  protected array $items = [];

  /**
   * {@inheritdoc}
   */
  public function getItem(string $scope, string|int $identifier): CalculatedPermissionsItemInterface|false {
    return $this->items[$scope][$identifier] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems(): array {
    $items = [];
    foreach ($this->items as $scope_items) {
      foreach ($scope_items as $item) {
        $items[] = $item;
      }
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function getScopes(): array {
    return array_keys($this->items);
  }

  /**
   * {@inheritdoc}
   */
  public function getItemsByScope($scope): array {
    return isset($this->items[$scope])
      ? array_values($this->items[$scope])
      : [];
  }

}
