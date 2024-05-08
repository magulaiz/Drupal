<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Unit\Plugin\display;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Tests\UnitTestCase;
use Drupal\views\Plugin\views\display\Page;
use Symfony\Component\Routing\Route;

/**
 * @group views
 */
#[CoversClass(\Drupal\views\Plugin\views\display\Page::class)]
class PageTest extends UnitTestCase {

  public function testBuildBasicRenderable() {
    $route = new Route('/test-view');
    $route->setDefault('view_id', 'test_view');
    $route->setOption('_view_display_plugin_id', 'page');
    $route->setOption('_view_display_show_admin_links', TRUE);

    $result = Page::buildBasicRenderable('test_view', 'page_1', [], $route);

    $this->assertEquals('test_view', $result['#view_id']);
    $this->assertEquals('page', $result['#view_display_plugin_id']);
    $this->assertEquals(TRUE, $result['#view_display_show_admin_links']);
  }

  public function testBuildBasicRenderableWithMissingRoute() {
    $this->expectException(\BadFunctionCallException::class);
    Page::buildBasicRenderable('test_view', 'page_1', []);
  }

}
