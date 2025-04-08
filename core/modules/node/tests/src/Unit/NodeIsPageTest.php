<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;

/**
 * @covers node_is_page
 *
 * @group node
 */
class NodeIsPageTest extends UnitTestCase {

  /**
   * Tests node_is_page.
   *
   * @param string $route_name
   *   The route name to test.
   * @param int $route_nid
   *   The nid returned from the mocked route.
   * @param int $check_nid
   *   The nid to test against.
   * @param bool $expected
   *   The expected result.
   *
   * @dataProvider routeNodeProvider
   */
  public function testNodeIsPage(string $route_name, int $route_nid, int $check_nid, bool $expected): void {
    require_once $this->root . '/core/modules/node/node.module';
    $container = new ContainerBuilder();
    $container->set('current_route_match', $this->setupCurrentRouteMatch($route_name, $route_nid));
    \Drupal::setContainer($container);
    $node = $this->setupNode($check_nid);
    $this->assertEquals($expected, \node_is_page($node));
  }

  /**
   * Data provider for self::testNodeIsPage().
   */
  public static function routeNodeProvider(): array {
    return [
      ['entity.node.canonical', 1, 1, TRUE],
      ['entity.node.canonical', 1, 2, FALSE],
      ['entity.node.latest_version', 1, 1, TRUE],
      ['entity.node.latest_version', 1, 2, FALSE],
      ['foo', 1, 1, FALSE],
    ];
  }

  /**
   * Mock the current route matching object.
   *
   * @param string $route_name
   *   The route to mock.
   * @param int $nid
   *   The node ID for mocking.
   *
   * @return \Drupal\Core\Routing\CurrentRouteMatch
   *   The mocked current route match object.
   */
  protected function setupCurrentRouteMatch(string $route_name, int $nid): CurrentRouteMatch {
    $route_match = $this->prophesize(CurrentRouteMatch::class);
    $route_match->getRouteName()->willReturn($route_name);
    $route_match->getParameter('node')->willReturn($this->setupNode($nid));

    return $route_match->reveal();
  }

  /**
   * Mock a node object.
   *
   * @param int $nid
   *   The node ID to mock.
   *
   * @return \Drupal\node\Entity\Node
   *   The mocked node.
   */
  protected function setupNode(int $nid): Node {
    $node = $this->prophesize(Node::class);
    $node->id()->willReturn($nid);

    return $node->reveal();
  }

}
