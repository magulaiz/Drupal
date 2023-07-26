<?php

namespace Drupal\Tests\Core\Session\Unit;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Cache\VariationCacheInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Session\AccessPolicyBase;
use Drupal\Core\Session\AccessPolicyChain;
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
 * Tests the AccessPolicyChain service.
 *
 * @coversDefaultClass \Drupal\Core\Session\AccessPolicyChain
 * @group Session
 */
class AccessPolicyChainTest extends UnitTestCase {

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
   * @covers ::getCalculators
   */
  public function testAddCalculator() {
    $chain_calculator = $this->setUpAccessPolicyChain();
    $calculators = [
      new FooAccessPolicy(),
      new BarAccessPolicy(),
      new BazAccessPolicy(),
      new BarAlterAccessPolicy(),
    ];

    foreach ($calculators as $calculator) {
      $chain_calculator->addAccessPolicy($calculator);
    }

    $this->assertEquals($calculators, $chain_calculator->getAccessPolicies(), 'The added access policies match the returned ones.');
  }

  /**
   * Tests that the persistent cache contexts are returned properly.
   *
   * @covers ::applies
   * @covers ::getPersistentCacheContexts
   */
  public function testGetPersistentCacheContexts() {
    $chain_calculator = $this->setUpAccessPolicyChain();

    foreach ([new FooAccessPolicy(), new BarAccessPolicy(), new BazAccessPolicy(), new BarAlterAccessPolicy()] as $calculator) {
      $chain_calculator->addAccessPolicy($calculator);
    }

    $this->assertEquals(['foo', 'bar'], $chain_calculator->getPersistentCacheContexts('anything'), 'Cache contexts match only those access policies that apply to the scope.');
  }

  /**
   * Tests that calculators are properly processed.
   *
   * @covers ::calculatePermissions
   */
  public function testCalculatePermissions() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $calculator = new BarAccessPolicy();

    $chain_calculator = $this->setUpAccessPolicyChain();
    $chain_calculator->addAccessPolicy($calculator);

    $calculator_permissions = $calculator->calculatePermissions($account, 'bar');
    $calculator_permissions->addCacheTags(['access_policies']);
    $this->assertEquals(new CalculatedPermissions($calculator_permissions), $chain_calculator->calculatePermissions($account, 'bar'));
  }

  /**
   * Tests that calculators that do not apply are not processed.
   *
   * @covers ::applies
   * @covers ::calculatePermissions
   */
  public function testCalculatePermissionsNoApply() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $calculator = new BarAccessPolicy();

    $chain_calculator = $this->setUpAccessPolicyChain();
    $chain_calculator->addAccessPolicy($calculator);

    $no_permissions = new RefinableCalculatedPermissions();
    $no_permissions->addCacheTags(['access_policies']);
    $calculated_permissions = $chain_calculator->calculatePermissions($account, 'nothing');
    $this->assertEquals(new CalculatedPermissions($no_permissions), $calculated_permissions);
  }

  /**
   * Tests that calculators can alter the final result.
   *
   * @covers ::calculatePermissions
   */
  public function testAlterPermissions() {
    $account = $this->prophesize(AccountInterface::class)->reveal();

    $chain_calculator = $this->setUpAccessPolicyChain();
    $chain_calculator->addAccessPolicy(new BarAccessPolicy());
    $chain_calculator->addAccessPolicy(new BarAlterAccessPolicy());

    $actual_permissions = $chain_calculator
      ->calculatePermissions($account, 'bar')
      ->getItem('bar', 1)
      ->getPermissions();

    $this->assertEquals(['foo', 'baz'], $actual_permissions);
  }

  /**
   * Tests that alters that do not apply are not processed.
   *
   * @covers ::calculatePermissions
   */
  public function testAlterPermissionsNoApply() {
    $account = $this->prophesize(AccountInterface::class)->reveal();

    $chain_calculator = $this->setUpAccessPolicyChain();
    $chain_calculator->addAccessPolicy($calculator = new FooAccessPolicy());
    $chain_calculator->addAccessPolicy(new BarAlterAccessPolicy());

    $calculator_permissions = $calculator->calculatePermissions($account, 'foo');
    $calculator_permissions->addCacheTags(['access_policies']);
    $this->assertEquals(new CalculatedPermissions($calculator_permissions), $chain_calculator->calculatePermissions($account, 'foo'));
  }

  /**
   * Tests that calculators which do nothing are properly processed.
   *
   * @covers ::calculatePermissions
   */
  public function testEmptyCalculator() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $calculator = new EmptyAccessPolicy();

    $chain_calculator = $this->setUpAccessPolicyChain();
    $chain_calculator->addAccessPolicy($calculator);

    $calculator_permissions = $calculator->calculatePermissions($account, 'anything');
    $calculator_permissions->addCacheTags(['access_policies']);
    $calculated_permissions = $chain_calculator->calculatePermissions($account, 'anything');
    $this->assertEquals(new CalculatedPermissions($calculator_permissions), $calculated_permissions);
  }

  /**
   * Tests that everything works if no calculators are present.
   *
   * @covers ::calculatePermissions
   */
  public function testNoCalculators() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $chain_calculator = $this->setUpAccessPolicyChain();

    $no_permissions = new RefinableCalculatedPermissions();
    $no_permissions->addCacheTags(['access_policies']);
    $calculated_permissions = $chain_calculator->calculatePermissions($account, 'anything');
    $this->assertEquals(new CalculatedPermissions($no_permissions), $calculated_permissions);
  }

  /**
   * Tests the wrong scope exception.
   *
   * @covers ::calculatePermissions
   */
  public function testWrongScopeException() {
    $chain_calculator = $this->setUpAccessPolicyChain();
    $chain_calculator->addAccessPolicy(new AlwaysAddsAccessPolicy());

    $this->expectException(AccessPolicyScopeException::class);
    $this->expectExceptionMessage(sprintf('The calculator "%s" returned permissions for scopes other than "%s".', AlwaysAddsAccessPolicy::class, 'bar'));
    $chain_calculator->calculatePermissions($this->prophesize(AccountInterface::class)->reveal(), 'bar');
  }

  /**
   * Tests the multiple scopes exception.
   *
   * @covers ::calculatePermissions
   */
  public function testMultipleScopeException() {
    $chain_calculator = $this->setUpAccessPolicyChain();
    $chain_calculator->addAccessPolicy(new FooAccessPolicy());
    $chain_calculator->addAccessPolicy(new AlwaysAddsAccessPolicy());

    $this->expectException(AccessPolicyScopeException::class);
    $this->expectExceptionMessage(sprintf('The calculator "%s" returned permissions for scopes other than "%s".', AlwaysAddsAccessPolicy::class, 'foo'));
    $chain_calculator->calculatePermissions($this->prophesize(AccountInterface::class)->reveal(), 'foo');
  }

  /**
   * Tests the multiple scopes exception.
   *
   * @covers ::calculatePermissions
   */
  public function testMultipleScopeAlterException() {
    $chain_calculator = $this->setUpAccessPolicyChain();
    $chain_calculator->addAccessPolicy(new FooAccessPolicy());
    $chain_calculator->addAccessPolicy(new AlwaysAltersAccessPolicy());

    $this->expectException(AccessPolicyScopeException::class);
    $this->expectExceptionMessage(sprintf('The calculator "%s" altered permissions in a scope other than "%s".', AlwaysAltersAccessPolicy::class, 'foo'));
    $chain_calculator->calculatePermissions($this->prophesize(AccountInterface::class)->reveal(), 'foo');
  }

  /**
   * Tests if the account switcher switches properly when user cache context is present.
   *
   * @covers ::calculatePermissions
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

    $chain_calculator = $this->setUpAccessPolicyChain(NULL, NULL, NULL, $account_switcher->reveal());
    $chain_calculator->addAccessPolicy(new BarAccessPolicy());
    if ($has_user_context) {
      $chain_calculator->addAccessPolicy(new UserContextAccessPolicy());
    }
    $chain_calculator->calculatePermissions($account, 'bar');
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
   * @covers ::calculatePermissions
   * @dataProvider cachingProvider
   */
  public function testCaching(bool $db_cache_hit, bool $static_cache_hit) {
    if ($static_cache_hit) {
      $this->assertFalse($db_cache_hit, 'DB cache should never be checked when there is a static hit.');
    }

    $account = $this->prophesize(AccountInterface::class)->reveal();
    $scope = 'bar';

    $bar_calculator = new BarAccessPolicy();
    $bar_permissions = $bar_calculator->calculatePermissions($account, $scope);
    $bar_permissions->addCacheTags(['access_policies']);
    $bar_permissions = new CalculatedPermissions($bar_permissions);

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
      $cache_static->set(Argument::any(), $bar_permissions, Argument::cetera())->shouldBeCalled();
    }
    else {
      $cache_item = new CacheItem($bar_permissions);
      $cache_static->get(Argument::cetera())->willReturn($cache_item);
      $cache_static->set()->shouldNotBeCalled();
    }
    $cache_static = $cache_static->reveal();
    $cache_db = $cache_db->reveal();

    $chain_calculator = $this->setUpAccessPolicyChain($cache_db, $cache_static);
    $chain_calculator->addAccessPolicy($bar_calculator);
    $permissions = $chain_calculator->calculatePermissions($account, $scope);
    $this->assertEquals($bar_permissions, $permissions, 'Cached permission matches calculated.');
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
   * Sets up the access policy chain.
   *
   * @return \Drupal\Core\Session\AccessPolicyChainInterface
   */
  protected function setUpAccessPolicyChain(
    VariationCacheInterface $variation_cache = NULL,
    VariationCacheInterface $variation_cache_static = NULL,
    CacheBackendInterface $cache_static = NULL,
    AccountSwitcherInterface $account_switcher = NULL
  ) {
    if (!isset($variation_cache)) {
      $variation_cache = $this->prophesize(VariationCacheInterface::class);
      $variation_cache->get(Argument::cetera())->willReturn(FALSE);
      $variation_cache->set(Argument::cetera())->willReturn(NULL);
      $variation_cache = $variation_cache->reveal();
    }

    if (!isset($variation_cache_static)) {
      $variation_cache_static = $this->prophesize(VariationCacheInterface::class);
      $variation_cache_static->get(Argument::cetera())->willReturn(FALSE);
      $variation_cache_static->set(Argument::cetera())->willReturn(NULL);
      $variation_cache_static = $variation_cache_static->reveal();
    }

    if (!isset($cache_static)) {
      $cache_static = $this->prophesize(CacheBackendInterface::class);
      $cache_static->get(Argument::cetera())->willReturn(FALSE);
      $cache_static->set(Argument::cetera())->willReturn(NULL);
      $cache_static = $cache_static->reveal();
    }

    if (!isset($account_switcher)) {
      $account_switcher = $this->prophesize(AccountSwitcherInterface::class)->reveal();
    }

    return new AccessPolicyChain(
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
    return $calculated_permissions->addItem(new CalculatedPermissionsItem('foo', 1, ['foo', 'bar'], TRUE));
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
    return $calculated_permissions->addItem(new CalculatedPermissionsItem('bar', 1, ['foo', 'bar']));
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
    return $calculated_permissions->addItem(new CalculatedPermissionsItem('baz', 1, ['baz']));
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
          $item->getScope(),
          $item->getIdentifier(),
          $permissions
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
    return $calculated_permissions->addItem(new CalculatedPermissionsItem('always', 1, ['always']));
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
    $calculated_permissions->addItem(new CalculatedPermissionsItem('always', 2, ['always']));
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
