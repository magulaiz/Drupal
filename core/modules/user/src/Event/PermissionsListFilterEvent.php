<?php

namespace Drupal\user\Event;

use Drupal\Component\EventDispatcher\Event;

class PermissionsListFilterEvent extends Event {

  /**
   * The permissions to filter.
   *
   * @var array
   */
  protected $permissions;

  /**
   * Constructs a permissions list filter event object.
   *
   * @param array $permissions
   *   The permissions to filter.
   */
  public function __construct(array $permissions) {
    $this->permissions = $permissions;
  }

  /**
   * Filters the permissions with a provided callback function.
   *
   * @param callable $callback
   *   The filter callback.
   */
  public function filter(callable $callback): void {
    $this->permissions = array_filter($this->permissions, $callback, ARRAY_FILTER_USE_BOTH);
  }

  /**
   * Gets the available permissions.
   *
   * @return array
   *   The available permissions.
   */
  public function getPermissions(): array {
    return $this->permissions;
  }

}
