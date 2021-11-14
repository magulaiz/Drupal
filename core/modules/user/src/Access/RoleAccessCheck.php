<?php

namespace Drupal\user\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;

/**
 * Determines access to routes based on roles.
 *
 * You can specify the '_role' key on route requirements. If you specify a
 * single role, users with that role with have access. If you specify multiple
 * ones you can conjunct them with AND by using a "," and with OR by using "+".
 */
class RoleAccessCheck implements AccessInterface {

  /**
   * Checks access.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, AccountInterface $account) {
    // Requirements just allow strings, so this might be a comma separated list.
    $rid_string = $route->getRequirement('_role');

    $explode_and = array_filter(array_map('trim', explode(',', $rid_string)));
    if (count($explode_and) > 1) {
      $diff = array_diff($explode_and, $account->getRoles());
      if (empty($diff)) {
        return AccessResult::allowed()->addCacheContexts(['user.roles']);
      }
      $last = array_pop($explode_and);
      $reason = sprintf('All of the %s and %s roles are required', implode(', ', $explode_and), $last);
    }
    else {
      $explode_or = array_filter(array_map('trim', explode('+', $rid_string)));
      $intersection = array_intersect($explode_or, $account->getRoles());
      if (!empty($intersection)) {
        return AccessResult::allowed()->addCacheContexts(['user.roles']);
      }
      elseif (count($explode_or) > 1) {
        $last = array_pop($explode_or);
        $reason = sprintf('One of the %s or %s roles are required', implode(', ', $explode_or), $last);
      }
      else {
        $reason = sprintf('The %s role is required', $rid_string);
      }
    }

    // If there is no allowed role, give other access checks a chance.
    return AccessResult::neutral($reason)->addCacheContexts(['user.roles']);
  }

}
