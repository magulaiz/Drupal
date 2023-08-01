<?php

namespace Drupal\Tests\Core\Session;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Cache\VariationCacheInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Session\AccessPolicyBase;
use Drupal\Core\Session\AccessPolicyProcessor;
use Drupal\Core\Session\AccessPolicyScopeException;
use Drupal\Core\Session\CalculatedPermissions;
use Drupal\Core\Session\CalculatedPermissionsInterface;
use Drupal\Core\Session\CalculatedPermissionsItem;
use Drupal\Core\Session\RefinableCalculatedPermissions;
use Drupal\Core\Session\RefinableCalculatedPermissionsInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests the AccessPolicyProcessor service.
 *
 * @coversDefaultClass \Drupal\Core\Session\AccessPolicyProcessor
 * @group Session
 */
class AccessPolicyProcessorTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $cache_context_manager = $this->prophesize(CacheContextsManager::class);
    $cache_context_manager->assertValidTokens(Argument::any())->willReturn(TRUE);

    $container = $this->prophesize(ContainerInterface::class);
    $container->get('cache_contexts_manager')->willReturn($cache_context_manager->reveal());
    \Drupal::setContainer($container->reveal());
  }

  /**
   * Tests that access policies are properly added and returned.
   *
   * @covers ::addAccessPolicy
   * @covers ::getAccessPolicies
   */
  public function testAddAccessPolicy() {
    $processor = $this->setUpAccessPolicyProcessor();
    $access_policies = [
      new FooAccessPolicy(),
      new BarAccessPolicy(),
      new BazAccessPolicy(),
      new BarAlterAccessPolicy(),
    ];

    foreach ($access_policies as $access_policy) {
      $processor->addAccessPolicy($access_policy);
    }

    $this->assertEquals($access_policies, $processor->getAccessPolicies(), 'The added access policies match the returned ones.');
  }

  /**
   * Tests that the persistent cache contexts are returned properly.
   *
   * @covers ::getPersistentCacheContexts
   */
  public function testGetPersistentCacheContexts() {
    $processor = $this->setUpAccessPolicyProcessor();

    foreach ([new FooAccessPolicy(), new BarAccessPolicy(), new BazAccessPolicy(), new BarAlterAccessPolicy()] as $access_policy) {
      $processor->addAccessPolicy($access_policy);
    }

    $this->assertEquals(['foo', 'bar'], $processor->getPersistentCacheContexts('anything'), 'Cache contexts match only those access policies that apply to the scope.');
  }

  /**
   * Tests that access policies are properly processed.
   *
   * @covers ::processAccessPolicies
   */
  public function testCalculatePermissions() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $access_policy = new BarAccessPolicy();

    $processor = $this->setUpAccessPolicyProcessor();
    $processor->addAccessPolicy($access_policy);

    $access_policy_permissions = $access_policy->calculatePermissions($account, 'bar');
    $access_policy_permissions->addCacheTags(['access_policies']);
    $this->assertEquals(new CalculatedPermissions($access_policy_permissions), $processor->processAccessPolicies($account, 'bar'));
  }

  /**
   * Tests that access policies that do not apply are not processed.
   *
   * @covers ::processAccessPolicies
   */
  public function testCalculatePermissionsNoApply() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $access_policy = new BarAccessPolicy();

    $processor = $this->setUpAccessPolicyProcessor();
    $processor->addAccessPolicy($access_policy);

    $no_permissions = new RefinableCalculatedPermissions();
    $no_permissions->addCacheTags(['access_policies']);
    $calculated_permissions = $processor->processAccessPolicies($account, 'nothing');
    $this->assertEquals(new CalculatedPermissions($no_permissions), $calculated_permissions);
  }

  /**
   * Tests that access policies can alter the final result.
   *
   * @covers ::processAccessPolicies
   */
  public function testAlterPermissions() {
    $account = $this->prophesize(AccountInterface::class)->reveal();

    $processor = $this->setUpAccessPolicyProcessor();
    $processor->addAccessPolicy(new BarAccessPolicy());
    $processor->addAccessPolicy(new BarAlterAccessPolicy());

    $actual_permissions = $processor
      ->processAccessPolicies($account, 'bar')
      ->getItem('bar', 1)
      ->getPermissions();

    $this->assertEquals(['foo', 'baz'], $actual_permissions);
  }

  /**
   * Tests that alters that do not apply are not processed.
   *
   * @covers ::processAccessPolicies
   */
  public function testAlterPermissionsNoApply() {
    $account = $this->prophesize(AccountInterface::class)->reveal();

    $processor = $this->setUpAccessPolicyProcessor();
    $processor->addAccessPolicy($access_policy = new FooAccessPolicy());
    $processor->addAccessPolicy(new BarAlterAccessPolicy());

    $access_policy_permissions = $access_policy->calculatePermissions($account, 'foo');
    $access_policy_permissions->addCacheTags(['access_policies']);
    $this->assertEquals(new CalculatedPermissions($access_policy_permissions), $processor->processAccessPolicies($account, 'foo'));
  }

  /**
   * Tests that access policies which do nothing are properly processed.
   *
   * @covers ::processAccessPolicies
   */
  public function testEmptyCalculator() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $access_policy = new EmptyAccessPolicy();

    $processor = $this->setUpAccessPolicyProcessor();
    $processor->addAccessPolicy($access_policy);

    $access_policy_permissions = $access_policy->calculatePermissions($account, 'anything');
    $access_policy_permissions->addCacheTags(['access_policies']);
    $calculated_permissions = $processor->processAccessPolicies($account, 'anything');
    $this->assertEquals(new CalculatedPermissions($access_policy_permissions), $calculated_permissions);
  }

  /**
   * Tests that everything works if no access policies are present.
   *
   * @covers ::processAccessPolicies
   */
  public function testNoCalculators() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $processor = $this->setUpAccessPolicyProcessor();

    $no_permissions = new RefinableCalculatedPermissions();
    $no_permissions->addCacheTags(['access_policies']);
    $calculated_permissions = $processor->processAccessPolicies($account, 'anything');
    $this->assertEquals(new CalculatedPermissions($no_permissions), $calculated_permissions);
  }

  /**
   * Tests the wrong scope exception.
   *
   * @covers ::processAccessPolicies
   */
  public function testWrongScopeException() {
    $processor = $this->setUpAccessPolicyProcessor();
    $processor->addAccessPolicy(new AlwaysAddsAccessPolicy());

    $this->expectException(AccessPolicyScopeException::class);
    $this->expectExceptionMessage(sprintf('The access policy "%s" returned permissions for scopes other than "%s".', AlwaysAddsAccessPolicy::class, 'bar'));
    $processor->processAccessPolicies($this->prophesize(AccountInterface::class)->reveal(), 'bar');
  }

  /**
   * Tests the multiple scopes exception.
   *
   * @covers ::processAccessPolicies
   */
  public function testMultipleScopeException() {
    $processor = $this->setUpAccessPolicyProcessor();
    $processor->addAccessPolicy(new FooAccessPolicy());
    $processor->addAccessPolicy(new AlwaysAddsAccessPolicy());

    $this->expectException(AccessPolicyScopeException::class);
    $this->expectExceptionMessage(sprintf('The access policy "%s" returned permissions for scopes other than "%s".', AlwaysAddsAccessPolicy::class, 'foo'));
    $processor->processAccessPolicies($this->prophesize(AccountInterface::class)->reveal(), 'foo');
  }

  /**
   * Tests the multiple scopes exception.
   *
   * @covers ::processAccessPolicies
   */
  public function testMultipleScopeAlterException() {
    $processor = $this->setUpAccessPolicyProcessor();
    $processor->addAccessPolicy(new FooAccessPolicy());
    $processor->addAccessPolicy(new AlwaysAltersAccessPolicy());

    $this->expectException(AccessPolicyScopeException::class);
    $this->expectExceptionMessage(sprintf('The access policy "%s" altered permissions in a scope other than "%s".', AlwaysAltersAccessPolicy::class, 'foo'));
    $processor->processAccessPolicies($this->prophesize(AccountInterface::class)->reveal(), 'foo');
  }

  /**
   * Tests if the account switcher switches properly when user cache context is present.
   *
   * @covers ::processAccessPolicies
   * @dataProvider accountSwitcherProvider
   */
  public function testAccountSwitcher($has_user_context) {
    $account = $this->prophesize(AccountInterface::class)->reveal();

    $account_switcher = $this->prophesize(AccountSwitcherInterface::class);
    if ($has_user_context) {
      $account_switcher->switchTo($account)->shouldBeCalledTimes(1);
      $account_switcher->switchBack()->shouldBeCalledTimes(1);
    }
    else {
      $account_switcher->switchTo($account)->shouldNotBeCalled();
      $account_switcher->switchBack()->shouldNotBeCalled();
    }

    $processor = $this->setUpAccessPolicyProcessor(NULL, NULL, NULL, $account_switcher->reveal());
    $processor->addAccessPolicy(new BarAccessPolicy());
    if ($has_user_context) {
      $processor->addAccessPolicy(new UserContextAccessPolicy());
    }
    $processor->processAccessPolicies($account, 'bar');
  }

  /**
   * Data provider for testAccountSwitcher().
   *
   * @return array
   *   A list of testAccountSwitcher method arguments.
   */
  public function accountSwitcherProvider() {
    $cases['no-user-context'] = [FALSE];
    $cases['user-context'] = [TRUE];
    return $cases;
  }

  /**
   * Tests if the account switcher switches properly when user cache context is present.
   *
   * @covers ::processAccessPolicies
   * @dataProvider cachingProvider
   */
  public function testCaching(bool $db_cache_hit, bool $static_cache_hit) {
    if ($static_cache_hit) {
      $this->assertFalse($db_cache_hit, 'DB cache should never be checked when there is a static hit.');
    }

    $account = $this->prophesize(AccountInterface::class)->reveal();
    $scope = 'bar';

    $bar_access_policy = new BarAccessPolicy();
    $bar_permissions = $bar_access_policy->calculatePermissions($account, $scope);
    $bar_permissions->addCacheTags(['access_policies']);
    $none_refinable_bar_permissions = new CalculatedPermissions($bar_permissions);

    $cache_static = $this->prophesize(VariationCacheInterface::class);
    $cache_db = $this->prophesize(VariationCacheInterface::class);
    if (!$static_cache_hit) {
      if (!$db_cache_hit) {
        $cache_db->get(Argument::cetera())->willReturn(FALSE);
        $cache_db->set(Argument::any(), $bar_permissions, Argument::cetera())->shouldBeCalled();
      }
      else {
        $cache_item = new CacheItem($bar_permissions);
        $cache_db->get(Argument::cetera())->willReturn($cache_item);
        $cache_db->set()->shouldNotBeCalled();
      }
      $cache_static->get(Argument::cetera())->willReturn(FALSE);
      $cache_static->set(Argument::any(), $none_refinable_bar_permissions, Argument::cetera())->shouldBeCalled();
    }
    else {
      $cache_item = new CacheItem($none_refinable_bar_permissions);
      $cache_static->get(Argument::cetera())->willReturn($cache_item);
      $cache_static->set()->shouldNotBeCalled();
    }
    $cache_static = $cache_static->reveal();
    $cache_db = $cache_db->reveal();

    $processor = $this->setUpAccessPolicyProcessor($cache_db, $cache_static);
    $processor->addAccessPolicy($bar_access_policy);
    $permissions = $processor->processAccessPolicies($account, $scope);
    $this->assertEquals($none_refinable_bar_permissions, $permissions, 'Cached permission matches calculated.');
  }

  /**
   * Data provider for testCaching().
   *
   * @return array
   *   A list of testAccountSwitcher method arguments.
   */
  public function cachingProvider() {
    $cases = [
      'no-cache' => [FALSE, FALSE],
      'static-cache-hit' => [FALSE, TRUE],
      'db-cache-hit' => [TRUE, FALSE],
    ];
    return $cases;
  }

  /**
   * Sets up the access policy processor.
   *
   * @return \Drupal\Core\Session\AccessPolicyProcessorInterface
   */
  protected function setUpAccessPolicyProcessor(
    VariationCacheInterface $variation_cache = NULL,
    VariationCacheInterface $variation_cache_static = NULL,
    CacheBackendInterface $cache_static = NULL,
    AccountSwitcherInterface $account_switcher = NULL
  ) {
    // Prophecy does not accept a willReturn call on a mocked method if said
    // method has a return type of void. However, without willReturn() or any
    // other will* call, the method mock will not be registered.
    $prophecy_workaround = function () {};

    if (!isset($variation_cache)) {
      $variation_cache = $this->prophesize(VariationCacheInterface::class);
      $variation_cache->get(Argument::cetera())->willReturn(FALSE);
      $variation_cache->set(Argument::cetera())->will($prophecy_workaround);
      $variation_cache = $variation_cache->reveal();
    }

    if (!isset($variation_cache_static)) {
      $variation_cache_static = $this->prophesize(VariationCacheInterface::class);
      $variation_cache_static->get(Argument::cetera())->willReturn(FALSE);
      $variation_cache_static->set(Argument::cetera())->will($prophecy_workaround);
      $variation_cache_static = $variation_cache_static->reveal();
    }

    if (!isset($cache_static)) {
      $cache_static = $this->prophesize(CacheBackendInterface::class);
      $cache_static->get(Argument::cetera())->willReturn(FALSE);
      $cache_static->set(Argument::cetera())->will($prophecy_workaround);
      $cache_static = $cache_static->reveal();
    }

    if (!isset($account_switcher)) {
      $account_switcher = $this->prophesize(AccountSwitcherInterface::class)->reveal();
    }

    return new AccessPolicyProcessor(
      $variation_cache,
      $variation_cache_static,
      $cache_static,
      $account_switcher
    );
  }

}

class FooAccessPolicy extends AccessPolicyBase {

  public function applies(string $scope): bool {
    return $scope === 'foo' || $scope === 'anything';
  }

  public function calculatePermissions(AccountInterface $account, string $scope): CalculatedPermissionsInterface {
    $calculated_permissions = parent::calculatePermissions($account, $scope);
    return $calculated_permissions->addItem(new CalculatedPermissionsItem(['foo', 'bar'], TRUE, 'foo', 1));
  }

  public function getPersistentCacheContexts(string $scope): array {
    return ['foo'];
  }

}

class BarAccessPolicy extends AccessPolicyBase {

  public function applies(string $scope): bool {
    return $scope === 'bar' || $scope === 'anything';
  }

  public function calculatePermissions(AccountInterface $account, string $scope): CalculatedPermissionsInterface {
    $calculated_permissions = parent::calculatePermissions($account, $scope);
    return $calculated_permissions->addItem(new CalculatedPermissionsItem(['foo', 'bar'], FALSE, 'bar', 1));
  }

  public function getPersistentCacheContexts(string $scope): array {
    return ['bar'];
  }

}

class BazAccessPolicy extends AccessPolicyBase {

  public function applies(string $scope): bool {
    return $scope === 'baz';
  }

  public function calculatePermissions(AccountInterface $account, string $scope): CalculatedPermissionsInterface {
    $calculated_permissions = parent::calculatePermissions($account, $scope);
    return $calculated_permissions->addItem(new CalculatedPermissionsItem(['baz'], FALSE, 'baz', 1));
  }

  public function getPersistentCacheContexts(string $scope): array {
    return ['baz'];
  }

}

class BarAlterAccessPolicy extends AccessPolicyBase {

  public function applies(string $scope): bool {
    return $scope === 'bar' || $scope === 'anything';
  }

  public function alterPermissions(RefinableCalculatedPermissionsInterface $calculated_permissions): void {
    parent::alterPermissions($calculated_permissions);

    foreach ($calculated_permissions->getItemsByScope('bar') as $item) {
      $permissions = $item->getPermissions();

      if (($key = array_search('bar', $permissions, TRUE)) !== FALSE) {
        $permissions[$key] = 'baz';

        $new_item = new CalculatedPermissionsItem(
          $permissions,
          FALSE,
          $item->getScope(),
          $item->getIdentifier()
        );

        $calculated_permissions->addItem($new_item, TRUE);
      }
    }
  }

}

class AlwaysAddsAccessPolicy extends AccessPolicyBase {

  public function applies(string $scope): bool {
    return TRUE;
  }

  public function calculatePermissions(AccountInterface $account, string $scope): CalculatedPermissionsInterface {
    $calculated_permissions = parent::calculatePermissions($account, $scope);
    return $calculated_permissions->addItem(new CalculatedPermissionsItem(['always'], FALSE, 'always', 1));
  }

  public function getPersistentCacheContexts(string $scope): array {
    return ['always'];
  }

}

class AlwaysAltersAccessPolicy extends AccessPolicyBase {

  public function applies(string $scope): bool {
    return TRUE;
  }

  public function alterPermissions(RefinableCalculatedPermissionsInterface $calculated_permissions): void {
    parent::alterPermissions($calculated_permissions);
    $calculated_permissions->addItem(new CalculatedPermissionsItem(['always'], FALSE, 'always', 2));
  }

  public function getPersistentCacheContexts(string $scope): array {
    return ['always'];
  }

}

class EmptyAccessPolicy extends AccessPolicyBase {}

class UserContextAccessPolicy extends AccessPolicyBase {

  public function applies(string $scope): bool {
    return TRUE;
  }

  public function getPersistentCacheContexts(string $scope): array {
    return ['user'];
  }

}

class CacheItem {

  public $data;

  public function __construct($data) {
    $this->data = $data;
  }

}
