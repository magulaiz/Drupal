<?php

namespace Drupal\Core\Session;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\VariationCacheInterface;

/**
 * Processes access policies into permissions for an account.
 */
class AccessPolicyProcessor implements AccessPolicyProcessorInterface {

  /**
   * The access policies.
   *
   * @var \Drupal\Core\Session\AccessPolicyInterface[]
   */
  protected array $accessPolicies = [];

  /**
   * Constructs an AccessPolicyChain object.
   *
   * @param \Drupal\Core\Cache\VariationCacheInterface $cache
   *   The variation cache backend to use as a persistent cache.
   * @param \Drupal\Core\Cache\VariationCacheInterface $static
   *   The variation cache backend to use as a static cache.
   * @param \Drupal\Core\Cache\CacheBackendInterface $regularStatic
   *   The regular cache backend to use as a static cache.
   * @param \Drupal\Core\Session\AccountSwitcherInterface $accountSwitcher
   *   The account switcher service.
   */
  public function __construct(
    protected VariationCacheInterface $cache,
    protected VariationCacheInterface $static,
    protected CacheBackendInterface $regularStatic,
    protected AccountSwitcherInterface $accountSwitcher) {
  }

  /**
   * {@inheritdoc}
   */
  public function addAccessPolicy(AccessPolicyInterface $access_policy): void {
    $this->accessPolicies[] = $access_policy;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessPolicies(): array {
    return $this->accessPolicies;
  }

  /**
   * {@inheritdoc}
   */
  public function processAccessPolicies(AccountInterface $account, string $scope = AccessPolicyInterface::SCOPE_DRUPAL): CalculatedPermissionsInterface {
    $persistent_cache_contexts = $this->getPersistentCacheContexts($scope);
    $initial_cacheability = (new CacheableMetadata())->addCacheContexts($persistent_cache_contexts);
    $cache_keys = ['access_policies', $scope];

    // Whether to switch the user account during cache storage and retrieval.
    //
    // This is necessary because permissions may be stored varying by the user
    // cache context or one of its child contexts. Because we may be calculating
    // permissions for an account other than the current user, we need to ensure
    // that the cache ID for said entry is set according to the passed in
    // account's data.
    //
    // Drupal core does not help us here because there is no way to reuse the
    // cache context logic outside of the caching layer. This means that in
    // order to generate a cache ID based on, let's say, one's permissions, we'd
    // have to copy all of the permission hash generation logic. Same goes for
    // the optimizing/folding of cache contexts.
    //
    // Instead of doing so, we simply set the current user to the passed in
    // account, calculate the cache ID and then immediately switch back. It's
    // the cleanest solution we could come up with that doesn't involve copying
    // half of core's caching layer and that still allows us to use the
    // VariationCache for accounts other than the current user.
    $switch_account = FALSE;
    foreach ($persistent_cache_contexts as $cache_context) {
      [$cache_context_root] = explode('.', $cache_context, 2);
      if ($cache_context_root === 'user') {
        $switch_account = TRUE;
        $this->accountSwitcher->switchTo($account);
        break;
      }
    }

    // Retrieve the permissions from the static cache if available.
    $static_cache_hit = FALSE;
    $persistent_cache_hit = FALSE;
    if ($static_cache = $this->static->get($cache_keys, $initial_cacheability)) {
      $static_cache_hit = TRUE;
      $calculated_permissions = $static_cache->data;
    }
    // Retrieve the permissions from the persistent cache if available.
    elseif ($cache = $this->cache->get($cache_keys, $initial_cacheability)) {
      $persistent_cache_hit = TRUE;
      $calculated_permissions = $cache->data;
    }
    // Otherwise build the permissions from scratch.
    else {
      // Build mode, allow all access policies to add initial data.
      $calculated_permissions = new RefinableCalculatedPermissions();
      foreach ($this->getAccessPolicies() as $access_policy) {
        if (!$access_policy->applies($scope)) {
          continue;
        }

        $policy_permissions = $access_policy->calculatePermissions($account, $scope);
        if (!$this->validateScope($scope, $policy_permissions)) {
          throw new AccessPolicyScopeException(sprintf('The access policy "%s" returned permissions for scopes other than "%s".', get_class($access_policy), $scope));
        }

        $calculated_permissions = $calculated_permissions->merge($policy_permissions);
      }

      // Alter mode, allow all access policies to alter the complete build.
      foreach ($this->getAccessPolicies() as $access_policy) {
        if (!$access_policy->applies($scope)) {
          continue;
        }

        $access_policy->alterPermissions($account, $calculated_permissions);
        if (!$this->validateScope($scope, $calculated_permissions)) {
          throw new AccessPolicyScopeException(sprintf('The access policy "%s" altered permissions in a scope other than "%s".', get_class($access_policy), $scope));
        }
      }

      // Apply a cache tag to easily flush the calculated permissions.
      $calculated_permissions->addCacheTags(['access_policies']);
    }

    if (!$static_cache_hit) {
      $cacheability = CacheableMetadata::createFromObject($calculated_permissions);

      // First store the actual calculated permissions in the persistent cache,
      // along with the final cache contexts after all calculations have run. We
      // need to store the RefinableCalculatedPermissions in the persistent
      // cache so we can still get the final cacheability from it for when we
      // run into a persistent cache hit but not a static one. At that point, if
      // we had stored a CalculatedPermissions object, we would no longer be
      // able to ask for its cache contexts.
      if (!$persistent_cache_hit) {
        $this->cache->set($cache_keys, $calculated_permissions, $cacheability, $initial_cacheability);
      }

      // Then convert the calculated permissions to an immutable value object
      // and store it in the static cache so that we don't have to do the same
      // conversion every time we call for the calculated permissions from a
      // warm static cache.
      $calculated_permissions = new CalculatedPermissions($calculated_permissions);
      $this->static->set($cache_keys, $calculated_permissions, $cacheability, $initial_cacheability);
    }

    if ($switch_account) {
      $this->accountSwitcher->switchBack();
    }

    // Return the permissions as an immutable value object.
    return $calculated_permissions;
  }

  /**
   * {@inheritdoc}
   */
  public function getPersistentCacheContexts(string $scope): array {
    $cid = 'access_policies:access_policy_processor:contexts:' . $scope;

    // Retrieve the contexts from the regular static cache if available.
    if ($static_cache = $this->regularStatic->get($cid)) {
      $contexts = $static_cache->data;
    }
    else {
      $contexts = [];
      foreach ($this->getAccessPolicies() as $access_policy) {
        if ($access_policy->applies($scope)) {
          $contexts = array_merge($contexts, $access_policy->getPersistentCacheContexts($scope));
        }
      }

      // Store the contexts in the regular static cache.
      $this->regularStatic->set($cid, $contexts);
    }

    return $contexts;
  }

  /**
   * Validates if calculated permissions all match a single scope.
   *
   * @param string $scope
   *   The scope to match.
   * @param \Drupal\Core\Session\CalculatedPermissionsInterface $calculated_permissions
   *   The calculated permissions that should match the scope.
   *
   * @return bool
   *   Whether the calculated permissions match the scope.
   */
  protected function validateScope(string $scope, CalculatedPermissionsInterface $calculated_permissions): bool {
    $actual_scopes = $calculated_permissions->getScopes();

    // Validate that only the requested scope was returned. An empty result is
    // allowed, however, as it might be that the access policy had nothing to
    // say for this scope.
    if (!empty($actual_scopes) && (count($actual_scopes) > 1 || reset($actual_scopes) !== $scope)) {
      return FALSE;
    }

    return TRUE;
  }

}
