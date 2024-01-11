<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Menu;

use Drupal\Core\Menu\MenuActiveTrail;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Tests\UnitTestCase;
use Drupal\TestTools\Random;
use Drupal\Core\Routing\RouteObjectInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;

/**
 * Tests the active menu trail service.
 *
 * @group Menu
 *
 * @coversDefaultClass \Drupal\Core\Menu\MenuActiveTrail
 */
class MenuActiveTrailTest extends UnitTestCase {

  /**
   * The tested active menu trail service.
   *
   * @var \Drupal\Core\Menu\MenuActiveTrail
   */
  protected $menuActiveTrail;

  /**
   * The test request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The mocked menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $menuLinkManager;

  /**
   * The mocked cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cache;

  /**
   * The mocked lock.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $lock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->requestStack = new RequestStack();
    $this->currentRouteMatch = new CurrentRouteMatch($this->requestStack);
    $this->menuLinkManager = $this->createMock('Drupal\Core\Menu\MenuLinkManagerInterface');
    $this->cache = $this->createMock('\Drupal\Core\Cache\CacheBackendInterface');
    $this->lock = $this->createMock('\Drupal\Core\Lock\LockBackendInterface');

    $this->menuActiveTrail = new MenuActiveTrail($this->menuLinkManager, $this->currentRouteMatch, $this->cache, $this->lock);

    $container = new Container();
    $container->set('cache_tags.invalidator', $this->createMock('\Drupal\Core\Cache\CacheTagsInvalidatorInterface'));
    \Drupal::setContainer($container);
  }

  /**
   * Provides test data for all test methods.
   *
   * @return array
   *   Returns a list of test data of which each is an array containing the
   *   following elements:
   *     - request: A request object.
   *     - links: An array of menu links keyed by ID.
   *     - menu_name: The active menu name.
   *     - expected_link: The expected active link for the given menu.
   */
  public static function provider() {
    $data = [];

    $mock_route = new Route('');

    $request = new Request();
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, 'baby_llama');
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, $mock_route);
    $request->attributes->set('_raw_variables', new InputBag([]));

    $link_1 = MenuLinkMock::create(['id' => 'baby_llama_link_1', 'route_name' => 'baby_llama', 'title' => 'Baby llama', 'parent' => 'mama_llama_link']);
    $link_2 = MenuLinkMock::create(['id' => 'baby_llama_link_2', 'route_name' => 'baby_llama', 'title' => 'Baby llama', 'parent' => 'papa_llama_link']);

    // @see \Drupal\Core\Menu\MenuLinkManagerInterface::getParentIds()
    $link_1_parent_ids = ['baby_llama_link_1', 'mama_llama_link', ''];
    $empty_active_trail = [''];

    // No active link is returned when zero links match the current route.
    $data[] = [$request, [], Random::machineName(), NULL, $empty_active_trail];

    // The first (and only) matching link is returned when one link matches the
    // current route.
    $data[] = [$request, ['baby_llama_link_1' => $link_1], Random::machineName(), $link_1, $link_1_parent_ids];

    // The first of multiple matching links is returned when multiple links
    // match the current route, where "first" is determined by sorting by key.
    $data[] = [$request, ['baby_llama_link_1' => $link_1, 'baby_llama_link_2' => $link_2], Random::machineName(), $link_1, $link_1_parent_ids];

    // No active link is returned in case of a 403.
    $request = new Request();
    $request->attributes->set('_exception_statuscode', 403);
    $data[] = [$request, FALSE, Random::machineName(), NULL, $empty_active_trail];

    // No active link is returned when the route name is missing.
    $request = new Request();
    $data[] = [$request, FALSE, Random::machineName(), NULL, $empty_active_trail];

    return $data;
  }

  /**
   * Tests getActiveLink().
   *
   * @covers ::getActiveLink
   * @dataProvider provider
   */
  public function testGetActiveLink(Request $request, $links, $menu_name, $expected_link) {
    $this->requestStack->push($request);
    if ($links !== FALSE) {
      $this->menuLinkManager->expects($this->exactly(2))
        ->method('loadLinksByRoute')
        ->with('baby_llama')
        ->willReturn($links);
    }
    // Test with menu name.
    $this->assertSame($expected_link, $this->menuActiveTrail->getActiveLink($menu_name));
    // Test without menu name.
    $this->assertSame($expected_link, $this->menuActiveTrail->getActiveLink());
  }

  /**
   * Tests getActiveTrailIds().
   *
   * @covers ::getActiveTrailIds
   * @dataProvider provider
   */
  public function testGetActiveTrailIds(Request $request, $links, $menu_name, $expected_link, $expected_trail) {
    $expected_trail_ids = array_combine($expected_trail, $expected_trail);

    $this->requestStack->push($request);
    if ($links !== FALSE) {
      // We expect exactly two calls, one for the first call, and one after the
      // cache clearing below.
      $this->menuLinkManager->expects($this->exactly(2))
        ->method('loadLinksByRoute')
        ->with('baby_llama')
        ->willReturn($links);
      if ($expected_link !== NULL) {
        $this->menuLinkManager->expects($this->exactly(2))
          ->method('getParentIds')
          ->willReturnMap([
            [$expected_link->getPluginId(), $expected_trail_ids],
          ]);
      }
    }

    // Call out the same twice in order to ensure that static caching works.
    $this->assertSame($expected_trail_ids, $this->menuActiveTrail->getActiveTrailIds($menu_name));
    $this->assertSame($expected_trail_ids, $this->menuActiveTrail->getActiveTrailIds($menu_name));

    $this->menuActiveTrail->clear();
    $this->assertSame($expected_trail_ids, $this->menuActiveTrail->getActiveTrailIds($menu_name));
  }

  /**
   * Tests that menus with numeric IDs are handled correctly.
   *
   * @see https://www.drupal.org/project/drupal/issues/3399221
   * @covers ::resolveCacheMiss
   */
  public function testParallelCacheBuildForNumericMenus(): void {
    // Set up a request with a route name and object.
    $mock_route = new Route('');
    /** @var \Symfony\Component\HttpFoundation\Request $request */
    $request = new Request();
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, 'baby_llama');
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, $mock_route);
    $this->requestStack->push($request);

    // Create a menu link within a menu with numeric ID.
    $link_1 = MenuLinkMock::create(['menu_name' => '123', 'id' => 'menu_link_1', 'route_name' => 'baby_llama']);

    // Setup the menu link manager to return the menu link.
    $this->menuLinkManager->expects($this->any())
      ->method('loadLinksbyRoute')
      ->with('baby_llama')
      ->willReturn([$link_1]);
    $this->menuLinkManager->expects($this->any())
      ->method('getParentIds')
      ->with($link_1->getPluginId())
      ->willReturn([$link_1->getPluginId() => $link_1->getPluginId()]);

    // Setup a common cache_menu "backend" for two requests (i.e. two MenuActiveTrail instances)
    $cid = 'active-trail:route:baby_llama:route_parameters:' . serialize([]);
    $cache_menu = [];
    // Two requests will "get" the cache 4 times (2 for lazyLoadCache and 2 for updateCache).
    $this->cache->expects($this->exactly(4))
      ->method('get')
      ->with($cid)
      ->willReturnCallback(function ($cid) use (&$cache_menu) {
          return $cache_menu[$cid] ?? FALSE;
      });
    // Two requests will "set" the cache 2 times (1 for each request).
    $this->cache->expects($this->any())
      ->method('set')
      ->with($cid)
      ->willReturnCallback(function ($cid, $data) use (&$cache_menu) {
          $cache_menu[$cid] = (object) [
            'created' => time(),
            'data' => $data,
          ];
      });

    // Two requests will acquire and release the lock twice (from updateCache).
    $this->lock->expects($this->exactly(2))
      ->method('acquire')
      ->willReturn(TRUE);
    $this->lock->expects($this->exactly(2))
      ->method('release');

    // Two requests have their own MenuActiveTrail instances.
    $menuActiveTrail1 = new MenuActiveTrail($this->menuLinkManager, $this->currentRouteMatch, $this->cache, $this->lock);
    $menuActiveTrail2 = new MenuActiveTrail($this->menuLinkManager, $this->currentRouteMatch, $this->cache, $this->lock);

    // Both requests will call getActiveTrailIds(). Since cache is empty, both will call the resolveCacheMiss
    $menuActiveTrail1->get($link_1->getMenuName());
    $menuActiveTrail2->get($link_1->getMenuName());

    // After first request ends, it will call updateCache() which will set the cache.
    $menuActiveTrail1->destruct();
    $first_cache = $cache_menu[$cid]->data;
    // After second request ends, it will call updateCache() which will set the cache.
    $menuActiveTrail2->destruct();
    $second_cache = $cache_menu[$cid]->data;

    // The cached data should be the same for both requests.
    $this->assertEquals($first_cache, $second_cache);
  }

  /**
   * Tests getCid()
   *
   * @covers ::getCid
   */
  public function testGetCid() {
    $data = $this->provider()[1];
    /** @var \Symfony\Component\HttpFoundation\Request $request */
    $request = $data[0];
    /** @var \Symfony\Component\Routing\Route $route */
    $route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT);
    $route->setPath('/test/{b}/{a}');
    $request->attributes->get('_raw_variables')->add(['b' => 1, 'a' => 0]);
    $this->requestStack->push($request);

    $this->menuLinkManager->expects($this->any())
      ->method('loadLinksByRoute')
      ->with('baby_llama')
      ->willReturn($data[1]);

    $expected_link = $data[3];
    $expected_trail = $data[4];
    $expected_trail_ids = array_combine($expected_trail, $expected_trail);

    $this->menuLinkManager->expects($this->any())
      ->method('getParentIds')
      ->willReturnMap([
        [$expected_link->getPluginId(), $expected_trail_ids],
      ]);

    $this->assertSame($expected_trail_ids, $this->menuActiveTrail->getActiveTrailIds($data[2]));

    $this->cache->expects($this->once())
      ->method('set')
      // Ensure we normalize the serialized data by sorting them.
      ->with('active-trail:route:baby_llama:route_parameters:' . serialize(['a' => 0, 'b' => 1]));
    $this->lock->expects($this->any())
      ->method('acquire')
      ->willReturn(TRUE);
    $this->menuActiveTrail->destruct();
  }

}
