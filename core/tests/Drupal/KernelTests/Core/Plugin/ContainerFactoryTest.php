<?php

namespace Drupal\KernelTests\Core\Plugin;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that plugins are correctly instantiated.
 *
 * @group Plugin
 */
class ContainerFactoryTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['autowire_test'];

  /**
   * Tests that ContainerFactory can create plugin instances.
   */
  public function testContainerFactory() {
    $plugin = $this->container->get('plugin.manager.block')->createInstance('container_factory');
    $this->assertSame($plugin->routeMatch, $this->container->get('current_route_match'));

    $plugin = $this->container->get('plugin.manager.block')->createInstance('autowire');
    $this->assertSame($plugin->routeMatch, $this->container->get('current_route_match'));

    $plugin = $this->container->get('plugin.manager.block')->createInstance('container_factory_subclass');
    $this->assertSame($plugin->routeMatch, $this->container->get('current_route_match'));
    $this->assertSame($plugin->eventDispatcher, $this->container->get('event_dispatcher'));

    $plugin = $this->container->get('plugin.manager.block')->createInstance('autowire_subclass');
    $this->assertSame($plugin->routeMatch, $this->container->get('current_route_match'));
    $this->assertSame($plugin->eventDispatcher, $this->container->get('event_dispatcher'));

    $plugin = $this->container->get('plugin.manager.block')->createInstance('fully_autowire');
    $this->assertSame($plugin->routeMatch, $this->container->get('current_route_match'));
    $this->assertSame($plugin->eventDispatcher, $this->container->get('event_dispatcher'));
  }

}
