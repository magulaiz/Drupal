<?php

namespace Drupal\Core\Session;

/**
 * Runs the added access policies until the full permissions are built.
 *
 * Each access policy in the chain can be another chain, which is why this
 * interface extends the access policy one.
 */
interface AccessPolicyChainInterface extends AccessPolicyInterface {

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
   *   The calculators.
   */
  public function getAccessPolicies(): array;

}
