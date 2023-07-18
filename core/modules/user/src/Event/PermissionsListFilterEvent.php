<?php

namespace Drupal\user\Event;

use Drupal\Component\EventDispatcher\Event;

class PermissionsListFilterEvent extends Event {

  protected $permissions;

  public function __construct($permissions) {
    $this->permissions = $permissions;
  }

  public function setPermissions($permissions) {
    $this->permissions = $permissions;
  }

  public function getPermissions() {
    return $this->permissions;
  }
}
