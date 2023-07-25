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
   *   Whether this calculator applies to the given scope.
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
   * Alter the permissions after all access policies have finished building them.
   *
   * @param \Drupal\Core\Session\RefinableCalculatedPermissionsInterface $calculated_permissions
   *   The completely built calculated permissions.
   */
  public function alterPermissions(RefinableCalculatedPermissionsInterface $calculated_permissions): void;

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
