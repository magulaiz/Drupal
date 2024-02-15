<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\user\PermissionHandlerInterface;
use Drupal\user\PermissionProvidersLocator;
use Drupal\user_permission_provider_test\PermissionCallbacks;
use Drupal\user_permission_provider_test\PermissionCallbacksFromExistingService;
use Drupal\user_permission_provider_test\PermissionCallbacksWithContainerInjection;
use Drupal\user_permission_provider_test\TestPermissionProvider;

/**
 * Testing for user permission providers.
 *
 * @group user
 */
final class UserPermissionProviderTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['system', 'user', 'user_permission_provider_test'];

  /**
   * Tests permissions from providers of various types.
   */
  public function testPermissionProvider(): void {
    $permissions = $this->permissionHandler()->getPermissions();

    // From a permission provider tagged service:
    $this->assertEquals('Test permission multiple 1', (string) $permissions['user_permission_provider_test permission 1']['title']);
    $this->assertNull($permissions['user_permission_provider_test permission 1']['description']);
    $this->assertEquals('user_permission_provider_test', $permissions['user_permission_provider_test permission 1']['provider']);

    $this->assertEquals('Test permission multiple 2', (string) $permissions['user_permission_provider_test permission 2']['title']);
    $this->assertNull($permissions['user_permission_provider_test permission 2']['description']);
    $this->assertEquals('user_permission_provider_test', $permissions['user_permission_provider_test permission 2']['provider']);

    $this->assertEquals('Test permission single 3', (string) $permissions['user_permission_provider_test permission 3']['title']);
    $this->assertNull($permissions['user_permission_provider_test permission 3']['description']);
    $this->assertEquals('user_permission_provider_test', $permissions['user_permission_provider_test permission 3']['provider']);

    // From a service created from a permission_callbacks entry.
    $this->assertEquals('permission_callbacks permission multiple 1', (string) $permissions['user_permission_provider_test permission_callbacks permission 1']['title']);
    $this->assertNull($permissions['user_permission_provider_test permission_callbacks permission 1']['description']);
    $this->assertEquals('user_permission_provider_test', $permissions['user_permission_provider_test permission_callbacks permission 1']['provider']);

    $this->assertEquals('permission_callbacks permission multiple 2', (string) $permissions['user_permission_provider_test permission_callbacks permission 2']['title']);
    $this->assertNull($permissions['user_permission_provider_test permission_callbacks permission 2']['description']);
    $this->assertEquals('user_permission_provider_test', $permissions['user_permission_provider_test permission_callbacks permission 2']['provider']);

    $this->assertEquals('permission_callbacks permission single 3', (string) $permissions['user_permission_provider_test permission_callbacks permission 3']['title']);
    $this->assertNull($permissions['user_permission_provider_test permission_callbacks permission 3']['description']);
    $this->assertEquals('user_permission_provider_test', $permissions['user_permission_provider_test permission_callbacks permission 3']['provider']);

    // From a service created with a permission_callbacks implementing ContainerInjectionInterface.
    $this->assertEquals('Container injection permission', (string) $permissions['container injection permission_callbacks permission']['title']);
    $this->assertNull($permissions['container injection permission_callbacks permission']['description']);
    $this->assertEquals('user_permission_provider_test', $permissions['container injection permission_callbacks permission']['provider']);

    // From an existing service referenced by permission_callbacks.
    $this->assertEquals('Existing service permission', (string) $permissions['existing service permission_callbacks permission']['title']);
    $this->assertNull($permissions['existing service permission_callbacks permission']['description']);
    $this->assertEquals('user_permission_provider_test', $permissions['existing service permission_callbacks permission']['provider']);
  }

  /**
   * Tests permission provider locator.
   *
   * @covers \Drupal\user\PermissionProvidersLocator
   */
  public function testPermissionLocator(): void {
    $testCallbacks = [];
    foreach ($this->permissionProviderLocator()->getPermissionProviders() as [$provider, $callback]) {
      if ($provider !== 'user_permission_provider_test') {
        continue;
      }

      $reflection = new \ReflectionFunction($callback);
      $testCallbacks[] = [$reflection->getClosureThis()::class, $reflection->getName()];
    }

    $this->assertEquals([
      [TestPermissionProvider::class, 'permissionsMultiple'],
      [TestPermissionProvider::class, 'permissionsSingle'],
      [PermissionCallbacks::class, 'permissionsMultiple'],
      [PermissionCallbacks::class, 'permissionsSingle'],
      [PermissionCallbacksWithContainerInjection::class, 'permissions'],
      [PermissionCallbacksFromExistingService::class, 'permissions'],
    ], $testCallbacks);
  }

  /**
   * Gets the permission handler service.
   */
  public function permissionHandler(): PermissionHandlerInterface {
    return \Drupal::service('user.permissions');
  }

  /**
   * Gets the permission provider locator.
   */
  public function permissionProviderLocator(): PermissionProvidersLocator {
    return \Drupal::service(PermissionProvidersLocator::class);
  }

}
