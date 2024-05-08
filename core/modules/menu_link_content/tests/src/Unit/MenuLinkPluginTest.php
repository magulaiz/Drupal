<?php

declare(strict_types=1);

namespace Drupal\Tests\menu_link_content\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;
use Drupal\Tests\UnitTestCase;

/**
 * @group Menu
 */
#[CoversClass(\Drupal\menu_link_content\Plugin\Menu\MenuLinkContent::class)]
class MenuLinkPluginTest extends UnitTestCase {

  public function testGetInstanceReflection() {
    /** @var \Drupal\menu_link_content\Plugin\Menu\MenuLinkContent $menu_link_content_plugin */
    $menu_link_content_plugin = $this->prophesize(MenuLinkContent::class);
    $menu_link_content_plugin->getDerivativeId()->willReturn('test_id');
    $menu_link_content_plugin = $menu_link_content_plugin->reveal();

    $class = new \ReflectionClass(MenuLinkContent::class);
    $instance_method = $class->getMethod('getUuid');

    $this->assertEquals('test_id', $instance_method->invoke($menu_link_content_plugin));
  }

}
