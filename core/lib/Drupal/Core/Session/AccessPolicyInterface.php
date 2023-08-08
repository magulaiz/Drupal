<?php

namespace Drupal\Core\Session;

/**
 * Defines the access policy interface.
 *
 * Make sure that when calculating permissions, you attach the right cacheable
 * metadata. This includes cache contexts if your implementation causes the
 * calculated permissions to vary by something. Any cache contexts defined in
 * the getPersistentCacheContexts() methods must also be added to the
 * corresponding calculated permissions but Drupal\Core\Session\AccessPolicyBase
 * takes care of this for you.
 *
 * Do NOT use any cache context that relies on calculated permissions in any of
 * the calculations as you'd end up in an infinite loop. E.g.: user.permissions.
 */
interface AccessPolicyInterface {

  /**
   * Scope ID for general Drupal access.
   */
  const SCOPE_DRUPAL = 'drupal';

  /**
   * Checks whether this access policy applies to a given scope.
   *
   * @param string $scope
   *   The scope to check for.
   *
   * @return bool
   *   Whether this access policy applies to the given scope.
   */
  public function applies(string $scope): bool;

  /**
   * Calculates the permissions for an account within a given scope.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account for which to calculate the permissions.
   * @param string $scope
   *   The scope to calculate the permissions for.
   *
   * @return \Drupal\Core\Session\CalculatedPermissionsInterface
   *   An object representing the permissions within the given scope.
   */
  public function calculatePermissions(AccountInterface $account, string $scope): CalculatedPermissionsInterface;

  /**
   * Alter the permissions after all policies have finished building them.
   *
   * This should only be used to revoke permissions. If you wish to add
   * permissions, it is advised to write another access policy that uses the
   * calculatePermissions method instead.
   *
   * Keep in mind that there are many ways to alter access policy results.
   * Because each access policy itself is a service, the best way to get rid of
   * a specific access policy's permissions as a whole, is by simply removing
   * said access policy in your module's service provider.
   *
   * A good example use case of alterPermissions would be to flat out revoke a
   * banned list of permissions outside of office hours. This would make it so
   * no-one can perform any destructive actions while the helpdesk is offline.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account for which to alter the permissions.
   * @param \Drupal\Core\Session\RefinableCalculatedPermissionsInterface $calculated_permissions
   *   The completely built calculated permissions.
   */
  public function alterPermissions(AccountInterface $account, RefinableCalculatedPermissionsInterface $calculated_permissions): void;

  /**
   * Gets the persistent cache contexts for a given scope.
   *
   * WARNING: These should never change based on anything other than the passed
   * in scope. If you make these cache contexts conditional, the cache might not
   * work properly and you are exposing your site to privilege escalation.
   *
   * @param string $scope
   *   The scope to get the persistent cache contexts for.
   *
   * @return string[]
   *   The persistent cache contexts.
   */
  public function getPersistentCacheContexts(string $scope): array;

}
