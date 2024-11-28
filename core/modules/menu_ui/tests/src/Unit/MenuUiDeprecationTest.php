<?php

declare(strict_types=1);

namespace Drupal\Tests\menu_ui\Unit;

use Drupal\menu_ui\Plugin\Menu\LocalAction\MenuLinkAdd;
use Drupal\Tests\UnitTestCase;

/**
 * Tests deprecated class.
 *
 * @group legacy
 * @group menu
 */
class MenuUiDeprecationTest extends UnitTestCase {

  /**
   * The tested Menu Link Add plugin.
   *
   * @var \Drupal\menu_ui\Plugin\Menu\LocalAction\MenuLinkAdd
   */
  protected $menuLinkAdd;

  /**
   * The used plugin configuration.
   *
   * @var array
   */
  protected $config = [];

  /**
   * The used plugin ID.
   *
   * @var string
   */
  protected $pluginId = 'menu_link_add';

  /**
   * The used plugin definition.
   *
   * @var array
   */
  protected $pluginDefinition = [
    'id' => 'menu_link_add',
  ];

  /**
   * The redirect destination.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * The mocked route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $routeProvider;

  protected function setUp(): void {
    parent::setUp();

    $this->routeProvider = $this->createMock('Drupal\Core\Routing\RouteProviderInterface');
    $this->redirectDestination = $this->createMock('Drupal\Core\Routing\RedirectDestinationInterface');
  }

  /**
   * Setups the local action default.
   */
  protected function setupLocalActionDefault(): void {
    $this->menuLinkAdd = new MenuLinkAdd($this->config, $this->pluginId, $this->pluginDefinition, $this->routeProvider, $this->redirectDestination);
  }

  /**
   * Test MenuLinkAdd class' deprecation.
   */
  public function testMenuLinkAddDeprecation(): void {
    $this->expectDeprecation('Drupal\menu_ui\Plugin\Menu\LocalAction\MenuLinkAdd is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use \Drupal\Core\Menu\LocalActionWithDestination instead. See https://www.drupal.org/node/3490245');

    $this->pluginDefinition['title'] = 'Example';
    $this->setupLocalActionDefault();
    $this->assertEquals('Example', $this->menuLinkAdd->getTitle());
  }

}
