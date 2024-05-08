<?php

declare(strict_types=1);

namespace Drupal\Tests\layout_builder\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Core\Routing\RouteObjectInterface;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\Routing\LayoutTempstoreRouteEnhancer;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * @group layout_builder
 */
#[CoversClass(\Drupal\layout_builder\Routing\LayoutTempstoreRouteEnhancer::class)]
class LayoutTempstoreRouteEnhancerTest extends UnitTestCase {

  public function testEnhance() {
    $section_storage = $this->prophesize(SectionStorageInterface::class);
    $layout_tempstore_repository = $this->prophesize(LayoutTempstoreRepositoryInterface::class);
    $layout_tempstore_repository->get($section_storage->reveal())->willReturn('the_return_value');

    $options = [
      'parameters' => [
        'section_storage' => [
          'layout_builder_tempstore' => TRUE,
        ],
      ],
    ];
    $route = new Route('/test/{id}/{literal}/{null}', [], [], $options);

    $defaults = [
      'section_storage' => $section_storage->reveal(),
      RouteObjectInterface::ROUTE_OBJECT => $route,
    ];

    $expected = [
      'section_storage' => 'the_return_value',
      RouteObjectInterface::ROUTE_OBJECT => $route,
    ];

    $enhancer = new LayoutTempstoreRouteEnhancer($layout_tempstore_repository->reveal());
    $result = $enhancer->enhance($defaults, new Request());
    $this->assertEquals($expected, $result);
  }

}
