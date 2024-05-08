<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Unit\Menu;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\Core\Utility\CallableResolver;
use Drupal\Tests\Core\Menu\MenuLinkMock;
use Drupal\Tests\UnitTestCase;

/**
 * @group Menu
 */
#[CoversClass(\Drupal\Core\Menu\MenuLinkTree::class)]
class MenuLinkTreeTest extends UnitTestCase {

  /**
   * The tested menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $menuLinkTree;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->menuLinkTree = new MenuLinkTree(
      $this->createMock('\Drupal\Core\Menu\MenuTreeStorageInterface'),
      $this->createMock('\Drupal\Core\Menu\MenuLinkManagerInterface'),
      $this->createMock('\Drupal\Core\Routing\RouteProviderInterface'),
      $this->createMock('\Drupal\Core\Menu\MenuActiveTrailInterface'),
      $this->createMock(CallableResolver::class)
    );

    $cache_contexts_manager = $this->getMockBuilder('Drupal\Core\Cache\Context\CacheContextsManager')
      ->disableOriginalConstructor()
      ->getMock();
    $cache_contexts_manager->method('assertValidTokens')->willReturn(TRUE);
    $container = new ContainerBuilder();
    $container->set('cache_contexts_manager', $cache_contexts_manager);
    \Drupal::setContainer($container);
  }

  /**
   *
   * @see \Drupal\menu_link_content\Tests\MenuLinkContentCacheabilityBubblingTest
   * @dataProvider providerTestBuildCacheability
   */
  public function testBuildCacheability($description, $tree, $expected_build, $access, array $access_cache_contexts = []) {
    if ($access !== NULL) {
      $access->addCacheContexts($access_cache_contexts);
    }
    $build = $this->menuLinkTree->build($tree);
    $this->assertEquals($expected_build, $build, $description);
  }

  /**
   * Provides the test cases to test for ::testBuildCacheability().
   *
   * As explained in the documentation for ::testBuildCacheability(), this
   * generates 1 + (3 * 2 * 3) = 19 test cases.
   *
   * @see testBuildCacheability
   */
  public static function providerTestBuildCacheability() {
    $base_expected_build_empty = [
      '#cache' => [
        'contexts' => [],
        'tags' => [],
        'max-age' => Cache::PERMANENT,
      ],
    ];
    $base_expected_build = [
      '#cache' => [
        'contexts' => [],
        'tags' => [
          'config:system.menu.mock',
        ],
        'max-age' => Cache::PERMANENT,
      ],
      '#sorted' => TRUE,
      '#menu_name' => 'mock',
      '#theme' => 'menu__mock',
      '#items' => [
        // To be filled when generating test cases, using $get_built_element().
      ],
    ];

    $get_built_element = function (MenuLinkTreeElement $element) {
      $return = [
        'attributes' => new Attribute(),
        'title' => $element->link->getTitle(),
        'url' => new Url($element->link->getRouteName(), $element->link->getRouteParameters(), ['set_active_class' => TRUE]),
        'below' => [],
        'original_link' => $element->link,
        'is_expanded' => FALSE,
        'is_collapsed' => FALSE,
        'in_active_trail' => FALSE,
      ];

      if ($element->hasChildren && !empty($element->subtree)) {
        $return['is_expanded'] = TRUE;
      }
      elseif ($element->hasChildren) {
        $return['is_collapsed'] = TRUE;
      }
      if ($element->inActiveTrail) {
        $return['in_active_trail'] = TRUE;
      }

      return $return;
    };

    // The three access scenarios described in this method's documentation.
    $access_scenarios = [
      [NULL, []],
      [AccessResult::allowed(), ['access:allowed']],
      [AccessResult::neutral(), ['access:neutral']],
    ];

    // The two links scenarios described in this method's documentation.
    $cache_defaults = ['cache_max_age' => Cache::PERMANENT, 'cache_tags' => []];
    $links_scenarios = [
      [
        MenuLinkMock::create(['id' => 'test.example1', 'route_name' => 'example1', 'title' => 'Example 1']),
        MenuLinkMock::create(['id' => 'test.example2', 'route_name' => 'example1', 'title' => 'Example 2', 'metadata' => ['cache_contexts' => ['llama']] + $cache_defaults]),
      ],
      [
        MenuLinkMock::create(['id' => 'test.example1', 'route_name' => 'example1', 'title' => 'Example 1', 'metadata' => ['cache_contexts' => ['foo']] + $cache_defaults]),
        MenuLinkMock::create(['id' => 'test.example2', 'route_name' => 'example1', 'title' => 'Example 2', 'metadata' => ['cache_contexts' => ['bar']] + $cache_defaults]),
      ],
    ];

    $data = [];

    // Empty tree.
    $data[] = [
      'description' => 'Empty tree.',
      'tree' => [],
      'expected_build' => $base_expected_build_empty,
      'access' => NULL,
      'access_cache_contexts' => [],
    ];

    for ($i = 0; $i < count($access_scenarios); $i++) {
      [$access, $access_cache_contexts] = $access_scenarios[$i];

      for ($j = 0; $j < count($links_scenarios); $j++) {
        $links = $links_scenarios[$j];

        // Single-element tree.
        $tree = [
          new MenuLinkTreeElement($links[0], FALSE, 0, FALSE, []),
        ];
        $tree[0]->access = $access;
        if ($access === NULL || $access->isAllowed()) {
          $expected_build = $base_expected_build;
          $expected_build['#items']['test.example1'] = $get_built_element($tree[0]);
        }
        else {
          $expected_build = $base_expected_build_empty;
        }
        $expected_build['#cache']['contexts'] = array_merge($expected_build['#cache']['contexts'], $access_cache_contexts, $links[0]->getCacheContexts());
        $data[] = [
          'description' => "Single-item tree; access=$i; link=$j.",
          'tree' => $tree,
          'expected_build' => $expected_build,
          'access' => $access,
          'access_cache_contexts' => $access_cache_contexts,
        ];

        // Single-level tree.
        $tree = [
          new MenuLinkTreeElement($links[0], FALSE, 0, FALSE, []),
          new MenuLinkTreeElement($links[1], FALSE, 0, FALSE, []),
        ];
        $tree[0]->access = $access;
        $expected_build = $base_expected_build;
        if ($access === NULL || $access->isAllowed()) {
          $expected_build['#items']['test.example1'] = $get_built_element($tree[0]);
        }
        $expected_build['#items']['test.example2'] = $get_built_element($tree[1]);
        $expected_build['#cache']['contexts'] = array_merge($expected_build['#cache']['contexts'], $access_cache_contexts, $links[0]->getCacheContexts(), $links[1]->getCacheContexts());
        $data[] = [
          'description' => "Single-level tree; access=$i; link=$j.",
          'tree' => $tree,
          'expected_build' => $expected_build,
          'access' => $access,
          'access_cache_contexts' => $access_cache_contexts,
        ];

        // Multi-level tree.
        $multi_level_root_a = MenuLinkMock::create(['id' => 'test.root_a', 'route_name' => 'root_a', 'title' => 'Root A']);
        $multi_level_root_b = MenuLinkMock::create(['id' => 'test.root_b', 'route_name' => 'root_b', 'title' => 'Root B']);
        $multi_level_parent_c = MenuLinkMock::create(['id' => 'test.parent_c', 'route_name' => 'parent_c', 'title' => 'Parent C']);
        $tree = [
          new MenuLinkTreeElement($multi_level_root_a, TRUE, 0, FALSE, [
            new MenuLinkTreeElement($multi_level_parent_c, TRUE, 0, FALSE, [
              new MenuLinkTreeElement($links[0], FALSE, 0, FALSE, []),
            ]),
          ]),
          new MenuLinkTreeElement($multi_level_root_b, TRUE, 0, FALSE, [
            new MenuLinkTreeElement($links[1], FALSE, 1, FALSE, []),
          ]),
        ];
        $tree[0]->subtree[0]->subtree[0]->access = $access;
        $expected_build = $base_expected_build;
        $expected_build['#items']['test.root_a'] = $get_built_element($tree[0]);
        $expected_build['#items']['test.root_a']['below']['test.parent_c'] = $get_built_element($tree[0]->subtree[0]);
        if ($access === NULL || $access->isAllowed()) {
          $expected_build['#items']['test.root_a']['below']['test.parent_c']['below']['test.example1'] = $get_built_element($tree[0]->subtree[0]->subtree[0]);
        }
        $expected_build['#items']['test.root_b'] = $get_built_element($tree[1]);
        $expected_build['#items']['test.root_b']['below']['test.example2'] = $get_built_element($tree[1]->subtree[0]);
        $expected_build['#cache']['contexts'] = array_merge($expected_build['#cache']['contexts'], $access_cache_contexts, $links[0]->getCacheContexts(), $links[1]->getCacheContexts());
        $data[] = [
          'description' => "Multi-level tree; access=$i; link=$j.",
          'tree' => $tree,
          'expected_build' => $expected_build,
          'access' => $access,
          'access_cache_contexts' => $access_cache_contexts,
        ];
      }
    }

    return $data;
  }

}
