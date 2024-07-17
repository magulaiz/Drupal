<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests node link IDs are unchanged by the switch to link providers.
 *
 * @todo Remove this test when node links IDs are switched to the common
 * pattern.
 *
 * @group node
 */
class NodeLinksProviderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
  }

  /**
   * Tests the IDs of node link plugins.
   */
  public function testNodeLinkPlugins() {
    // Test the menu links.
    $menu_link_manager = $this->container->get('plugin.manager.menu.link');
    $menu_links = $menu_link_manager->getDefinitions();

    $this->assertArrayHasKey('entity.node_type.collection', $menu_links);

    // Test the menu actions.
    $action_link_manager = $this->container->get('plugin.manager.menu.local_action');
    $action_links = $action_link_manager->getDefinitions();

    $this->assertArrayHasKey('node.type_add', $action_links);
    $this->assertArrayHasKey('node.add_page', $action_links);

    // Test the menu tasks.
    $task_link_manager = $this->container->get('plugin.manager.menu.local_task');
    $task_links = $task_link_manager->getDefinitions();

    // Node type tasks.
    $this->assertArrayHasKey('entity.node_type.collection', $task_links);
    $this->assertArrayHasKey('entity.node_type.edit_form', $task_links);

    // Node tasks.
    $this->assertArrayHasKey('entity.node.canonical', $task_links);
    $this->assertArrayHasKey('entity.node.edit_form', $task_links);
    $this->assertArrayHasKey('entity.node.delete_form', $task_links);
    $this->assertArrayHasKey('entity.node.version_history', $task_links);
  }

}
