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
   * Updates permissions.
   *
   * @param array $permissions
   *   The updated permissions.
   */
  public function setPermissions($permissions) {
    $this->permissions = $permissions;
  }

  /**
   * Gets the permissions.
   *
   * @return array
   *   The value of the permission property.
   */
  public function getPermissions() {
    return $this->permissions;
  }

}
