<?php

namespace Drupal\Tests\system\Kernel\Entity;

use Drupal\Component\Utility\Html;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\NodeType;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests core's entity links provider handlers.
 *
 * @group entity
 */
class EntityLinksProviderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'node',
    'entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('entity_ui_test');
    $this->installEntitySchema('entity_ui_test_type');
  }

  /**
   * Tests that link plugins are created for the two test entity types.
   */
  public function testLinkPlugins() {
    // Test the menu links.
    $menu_link_manager = $this->container->get('plugin.manager.menu.link');
    $menu_links = $menu_link_manager->getDefinitions();

    $this->assertArrayHasKey('system.entity:entity.entity_ui_test_type.collection', $menu_links);

    // Test the menu actions.
    $action_link_manager = $this->container->get('plugin.manager.menu.local_action');
    $action_links = $action_link_manager->getDefinitions();

    $this->assertArrayHasKey('system.entity:entity.entity_ui_test.add', $action_links);
    $this->assertArrayHasKey('system.entity:entity.entity_ui_test_type.add', $action_links);

    // Test the menu tasks.
    $task_link_manager = $this->container->get('plugin.manager.menu.local_task');
    $task_links = $task_link_manager->getDefinitions();

    // Content entity tasks.
    $this->assertArrayHasKey('system.entity:entity.entity_ui_test.canonical', $task_links);
    $this->assertArrayHasKey('system.entity:entity.entity_ui_test.edit_form', $task_links);
    $this->assertArrayHasKey('system.entity:entity.entity_ui_test.delete_form', $task_links);

    // Config entity tasks.
    $this->assertArrayHasKey('system.entity:entity.entity_ui_test.edit_form', $task_links);
  }

}
