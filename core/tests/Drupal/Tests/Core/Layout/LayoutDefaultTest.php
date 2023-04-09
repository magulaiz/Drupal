<?php

namespace Drupal\Tests\Core\Layout;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Layout\LayoutDefinition;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Route;

/**
 * @coversDefaultClass \Drupal\Core\Layout\LayoutDefault
 * @group Layout
 */
class LayoutDefaultTest extends UnitTestCase {

  /**
   * @covers ::build
   * @dataProvider providerTestBuild
   */
  public function testBuild($regions, $expected) {
    \Drupal::setContainer(new ContainerBuilder());
    $route_matcher = $this->prophesize(RouteMatchInterface::class);
    $route_matcher->getRouteObject()->willReturn(new Route('layout_builder.add_section'));
    \Drupal::getContainer()->set('current_route_match', $route_matcher->reveal());

    $definition = new LayoutDefinition([
      'theme_hook' => 'layout',
      'library' => 'core/drupal',
      'regions' => [
        'left' => [
          'label' => 'Left',
        ],
        'right' => [
          'label' => 'Right',
        ],
      ],
    ]);
    $expected += [
      '#in_preview' => FALSE,
      '#settings' => [
        'label' => '',
      ],
      '#layout' => $definition,
      '#cache' => [
        'contexts' => [],
        'tags' => [],
        'max-age' => Cache::PERMANENT,
      ],
    ];

    $layout = new LayoutDefault([], '', $definition);
    $this->assertEquals($expected, $layout->build($regions));
  }

  /**
   * Provides test data for ::testBuild().
   */
  public function providerTestBuild() {
    // Sections with only empty blocks are not printed, but their cache info is.
    $data['empty_blocks'] = [
      [
        'right' => [
          ['#cache' => ['max-age' => 4133]],
        ],
      ],
      [
        '#cache' => [
          'contexts' => [],
          'tags' => [],
          'max-age' => 4133,
        ],
      ],
    ];
    // Empty regions are not added.
    $data['right_only'] = [
      [
        'right' => [
          ['foo' => 'bar'],
        ],
      ],
      [
        'right' => [
          ['foo' => 'bar'],
        ],
        '#theme' => 'layout',
        '#attached' => [
          'library' => [
            'core/drupal',
          ],
        ],
      ],
    ];
    // Regions will be in the order defined by the layout.
    $data['switched_order'] = [
      [
        'right' => [
          ['foo' => 'bar'],
        ],
        'left' => [
          ['foo' => 'baz'],
        ],
      ],
      [
        'left' => [
          ['foo' => 'baz'],
        ],
        'right' => [
          ['foo' => 'bar'],
        ],
        '#theme' => 'layout',
        '#attached' => [
          'library' => [
            'core/drupal',
          ],
        ],
      ],
    ];
    return $data;
  }

}
