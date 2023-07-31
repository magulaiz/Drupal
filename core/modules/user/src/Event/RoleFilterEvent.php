<?php

namespace Drupal\user\Event;

use Drupal\Component\EventDispatcher\Event;

class RoleFilterEvent extends Event {

  /**
   * The roles to filter.
   *
   * @var array
   */
  protected $roleList;

  /**
   * Constructs a role filter event object.
   *
   * @param array $roles
   *   The roles to filter.
   */
  public function __construct(array $roles) {
    $this->roleList = $roles;
  }

  /**
   * Filters the roles with a provided callback function.
   *
   * @param callable $callback
   *   The filter callback.
   *
   *   This is a callback used by array_filter and applied to a roles
   *   array. The ARRAY_FILTER_USE_BOTH option is used, so the function
   *   is effectively `callable(\Drupal\user\Entity\Role, string): bool`.
   *
   *    @code
   *   // Example:
   *   function filterAnonuymousAndAdmin(Role $role, string $role_name) {
   *     // Remove any role that starts with 'anonymous'
   *     if (strpos($permission_name, 'anonymous') === 0) {
   *       return FALSE;
   *     }
   *     // Remove any role that is an admin role.
   *     if ($role->isAdmin()) {
   *       return FALSE;
   *     }
   *     return TRUE;
   *   }
   *
   *   $permission_list_filter_event->filter('filterDeleteAndBlockContent');
   *
   * @endcode
   */
  public function filter(callable $callback): void {
    $this->roleList = array_filter($this->roleList, $callback, ARRAY_FILTER_USE_BOTH);
  }

  /**
   * Gets the available roles.
   *
   * @return array
   *   The available roles.
   */
  public function getRoles(): array {
    return $this->roleList;
  }
}
