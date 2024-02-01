<?php

namespace Drupal\Tests\Core\Routing;

use Drupal\Core\Routing\AttributeRouteDiscovery;
use Drupal\Core\Routing\UnsupportedRouteAttributePropertyException;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @coversDefaultClass \Drupal\Core\Routing\AttributeRouteDiscovery
 *
 * @group Routing
 */
class UnsupportedRouteAttributePropertiesTest extends UnitTestCase {

  /**
   * @covers ::createRouteCollection
   * @dataProvider providerTestException
   */
  public function testException($class, $message): void {
    $discovery = new AttributeRouteDiscovery(new \ArrayIterator());
    $reflection = new \ReflectionClass($discovery);
    $method = $reflection->getMethod('createRouteCollection');
    $this->expectException(UnsupportedRouteAttributePropertyException::class);
    $this->expectExceptionMessage($message);
    $method->invoke($discovery, $class);
  }

  public function providerTestException(): array {
    return [
      'method: locale' => [
        MethodRouteLocale::class,
        'The "locale" route attribute is not supported on route "MethodRouteLocale" in "Drupal\Tests\Core\Routing\MethodRouteLocale::attributeMethod()"',
      ],
      'method: localized_paths' => [
        MethodRouteLocalizedPaths::class,
        'The "path" route attribute does not support arrays on route "MethodRouteLocalizedPaths" in "Drupal\Tests\Core\Routing\MethodRouteLocalizedPaths::attributeMethod()"',
      ],
      'class: locale' => [
        ClassRouteLocale::class,
        'The "locale" route attribute is not supported in class "Drupal\Tests\Core\Routing\ClassRouteLocale"',
      ],
      'class: localized_paths' => [
        ClassRouteLocalizedPaths::class,
        'The "path" route attribute does not support arrays in class "Drupal\Tests\Core\Routing\ClassRouteLocalizedPaths"',
      ],
    ];
  }

}

class MethodRouteLocale {

  #[Route('/test_method_attribute', 'MethodRouteLocale', locale: 'de')]
  public function attributeMethod() {
    return ['#markup' => 'Testing method with a Route attribute'];
  }

}

class MethodRouteLocalizedPaths {

  #[Route(['de' => '/test_method_attribute'], 'MethodRouteLocalizedPaths')]
  public function attributeMethod() {
    return ['#markup' => 'Testing method with a Route attribute'];
  }

}

#[Route(locale: 'de')]
class ClassRouteLocale {

  #[Route('/test_method_attribute')]
  public function attributeMethod() {
    return ['#markup' => 'Testing method with a Route attribute'];
  }

}

#[Route(['de' => 'prefix/'])]
class ClassRouteLocalizedPaths {

  #[Route('/test_method_attribute')]
  public function attributeMethod() {
    return ['#markup' => 'Testing method with a Route attribute'];
  }

}
