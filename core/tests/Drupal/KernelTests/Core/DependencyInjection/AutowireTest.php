<?php

namespace Drupal\KernelTests\Core\DependencyInjection;

use Drupal\autowire_test\TestInjection;
use Drupal\autowire_test\TestInjection2;
use Drupal\autowire_test\TestService;
use Drupal\Core\Serialization\Yaml;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests auto-wiring services.
 *
 * @group DependencyInjection
 */
class AutowireTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['autowire_test'];

  /**
   * Tests that 'autowire_test.service' has its dependencies injected.
   */
  public function testAutowire(): void {
    // Ensure an autowired interface works.
    $this->assertInstanceOf(TestInjection::class, $this->container->get(TestService::class)->getTestInjection());
    // Ensure an autowired class works.
    $this->assertInstanceOf(TestInjection2::class, $this->container->get(TestService::class)->getTestInjection2());
  }

  /**
   * Tests that core services have aliases correctly defined where possible.
   */
  public function testCoreServiceAliases(): void {
    $services = [];
    $aliases = [];
    foreach (Yaml::decode(file_get_contents('core/core.services.yml'))['services'] as $id => $service) {
      if (is_string($service)) {
        $aliases[$id] = substr($service, 1);
      }
      elseif (isset($service['class']) && class_exists($service['class']) && empty($service['tags']) && (!isset($service['public']) || $service['public'])) {
        $services[$id] = $service['class'];
      }
    }

    $interfaces = [];
    foreach (get_declared_classes() as $class) {
      // Ignore proxy classes for autowiring purposes.
      if (strpos($class, '\\ProxyClass\\') !== FALSE) {
        continue;
      }

      foreach (class_implements($class) as $interface) {
        $interfaces[$interface][] = $class;
      }
    }

    $expected = [];
    foreach ($services as $id => $class) {
      // Skip services that share a class.
      if (count(array_keys($services, $class)) > 1) {
        continue;
      }

      // Expect standalone classes to be aliased.
      $implements = class_implements($class);
      if (!$implements) {
        $expected[$class] = $id;
      }

      // Expect classes that are the only implementation of their interface to
      // be aliased.
      foreach ($implements as $interface) {
        if (count($interfaces[$interface]) === 1) {
          $expected[$interface] = $id;
        }
      }
    }

    $this->assertSame($expected, array_intersect($expected, $aliases));
  }

}
