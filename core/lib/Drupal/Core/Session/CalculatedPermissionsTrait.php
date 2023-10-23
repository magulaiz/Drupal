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
  public function getItem(string $scope = AccessPolicyInterface::SCOPE_DRUPAL, string|int $identifier = AccessPolicyInterface::SCOPE_DRUPAL): CalculatedPermissionsItemInterface|false {
    return $this->items[$scope][$identifier] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems(): array {
    return array_reduce($this->items, fn($carry, $scope_items) => [...$carry, ...$scope_items], []);
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
  public function getItemsByScope(string $scope = AccessPolicyInterface::SCOPE_DRUPAL): array {
    return isset($this->items[$scope])
      ? array_values($this->items[$scope])
      : [];
  }

}
