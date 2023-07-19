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
   * Sets the permissions property to a new value.
   *
   * @param array $permissions
   *   The value the permissions property will be updated to.
   */
  public function setPermissions($permissions) {
    $this->permissions = $permissions;
  }

  /**
   * Gets the permission property value.
   *
   * @return array
   *   The value of the permission property.
   */
  public function getPermissions() {
    return $this->permissions;
  }

}
