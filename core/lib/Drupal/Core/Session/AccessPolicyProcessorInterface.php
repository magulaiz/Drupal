<?php

namespace Drupal\Core\Session;

/**
 * Processes all added access policies until the full permissions are built.
 */
interface AccessPolicyProcessorInterface {

  /**
   * Adds an access policy.
   *
   * @param \Drupal\Core\Session\AccessPolicyInterface $access_policy
   *   The access policy.
   */
  public function addAccessPolicy(AccessPolicyInterface $access_policy): void;

  /**
   * Gets all added access policies.
   *
   * @return \Drupal\Core\Session\AccessPolicyInterface[]
   *   The access policies.
   */
  public function getAccessPolicies(): array;

  /**
   * Processes the access policies for an account within a given scope.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account for which to calculate the permissions.
   * @param string $scope
   *   (optional) The scope to calculate the permissions, defaults to 'drupal'.
   *
   * @return \Drupal\Core\Session\CalculatedPermissionsInterface
   *   The access policies' permissions within the given scope.
   */
  public function processAccessPolicies(AccountInterface $account, string $scope = AccessPolicyInterface::SCOPE_DRUPAL): CalculatedPermissionsInterface;

  /**
   * Gets the persistent cache contexts of all policies within a given scope.
   *
   * @param string $scope
   *   The scope to get the persistent cache contexts for.
   *
   * @return string[]
   *   The persistent cache contexts of all policies within the scope.
   */
  public function getPersistentCacheContexts(string $scope): array;

}
