<?php

declare(strict_types=1);

namespace Drupal\Tests\serialization\Kernel;

use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that the user routes can be altered.
 *
 * @group serialization
 */
class UserRouteAlterTest extends KernelTestBase {

  /**
   * Modules to install.
   *
   * Keep the 'user_route_alter_test' before the 'serialization'. It
   * needs to alter the routes before the serialization module is
   * enabled so that it gets a chance to alter the routes.
   *
   * @var array
   */
  protected static $modules = [
    'user_route_alter_test',
    'serialization',
    'user',
  ];

  /**
   * The route provider service.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected RouteProviderInterface $routeProvider;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->routeProvider = $this->container->get('router.route_provider');
  }

  /**
   * Tests the altered 'user.login.http' route.
   */
  public function testUserAlteredRoute(): void {
    $route = $this->routeProvider->getRouteByName('user.login.http');
    $requirements = $route->getRequirements();

    $this->assertArrayHasKey('_format', $requirements, 'user.login.http route has "_format" requirement');
    $this->assertEquals('json|xml', $requirements['_format'], 'user.login.http route "_format" requirement is "json|xml"');
  }

  /**
   * Tests the altered 'user.pass.http' route missing the '_format'.
   *
   * The '_format' has been removed by the 'user_route_alter_test' module.
   */
  public function testUserAlteredRouteWithoutFormat(): void {
    $route = $this->routeProvider->getRouteByName('user.pass.http');
    $requirements = $route->getRequirements();

    $this->assertArrayHasKey('_access', $requirements, 'user.pass.http route has "_access" requirement');
    $this->assertEquals('FALSE', $requirements['_access'], 'user.pass.http route "_access" requirement is "FALSE"');
    $this->assertEquals('json|xml', $requirements['_format'], 'user.login.http route "_format" requirement is "json|xml"');
  }

}
