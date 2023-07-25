<?php

namespace Drupal\Core\Session;

/**
 * Represents a single entry for the calculated permissions.
 *
 * @see \Drupal\Core\Session\ChainPermissionCalculator
 */
class CalculatedPermissionsItem implements CalculatedPermissionsItemInterface {

  /**
   * Constructs a new CalculatedPermissionsItem.
   *
   * @param string $scope
   *   The scope name.
   * @param string|int $identifier
   *   The identifier within the scope.
   * @param string[] $permissions
   *   The permission names.
   * @param bool $isAdmin
   *   (optional) Whether the item grants admin privileges.
   */
  public function __construct(
    protected string $scope,
    protected string|int $identifier,
    protected array $permissions,
    protected bool $isAdmin = FALSE
  ) {
    $this->permissions = $this->isAdmin ? [] : array_unique($this->permissions);
  }

  /**
   * {@inheritdoc}
   */
  public function getScope(): string {
    return $this->scope;
  }

  /**
   * {@inheritdoc}
   */
  public function getIdentifier(): string|int {
    return $this->identifier;
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissions(): array {
    return $this->permissions;
  }

  /**
   * {@inheritdoc}
   */
  public function isAdmin(): bool {
    return $this->isAdmin;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPermission(string $permission): bool {
    return $this->isAdmin() || in_array($permission, $this->permissions, TRUE);
  }

}
