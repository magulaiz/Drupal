<?php

namespace Drupal\Tests\Core\Routing;

use Drupal\Core\Routing\AttributeRouteDiscovery;
use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Routing\RouteCollection;

/**
 * @coversDefaultClass \Drupal\Core\Routing\AttributeRouteDiscovery
 *
 * @group Routing
 */
class AttributeRouteDiscoveryTest extends UnitTestCase {

  /**
   * @covers ::onRouteBuild
   */
  public function testOnRouteBuild(): void {

    $event = new RouteBuildEvent(new RouteCollection());

    $namespaces = new \ArrayObject([
      'Drupal\router_test' => $this->root . '/core/modules/system/tests/modules/router_test_directory/src',
    ]);
    $discovery = new AttributeRouteDiscovery($namespaces);

    $discovery->onRouteBuild($event);

    $routeCollection = $event->getRouteCollection();
    $this->assertNotEmpty($routeCollection);

    // cspell:ignore testattributes attributemethod
    $route1 = $routeCollection->get('drupal_router_test_controller_testattributes_attributemethod');
    $this->assertNotNull($route1);
    $this->assertEquals('/test_method_attribute', $route1->getPath());
    $this->assertEquals('Drupal\router_test\Controller\TestAttributes::attributeMethod', $route1->getDefault('_controller'));
    $this->assertEquals("TRUE", $route1->getRequirement('_access'));

    // cspell:ignore testclassattribute
    $route2 = $routeCollection->get('drupal_router_test_controller_testclassattribute___invoke');
    $this->assertNotNull($route2);
    $this->assertEquals('/test_class_attribute', $route2->getPath());
    $this->assertEquals('Drupal\router_test\Controller\TestClassAttribute', $route2->getDefault('_controller'));
    $this->assertEquals("TRUE", $route2->getRequirement('_access'));
  }

}
