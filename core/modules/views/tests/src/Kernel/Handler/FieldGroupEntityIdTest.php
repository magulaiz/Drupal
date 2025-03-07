<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Kernel\Handler;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Views;

/**
 * Tests the "Group column: Entity ID" setting.
 *
 * @see \Drupal\views\Plugin\views\field\EntityField
 *
 * @group views
 */
class FieldGroupEntityIdTest extends ViewsKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field',
    'filter',
    'node',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  public static $testViews = ['test_group_entity_id'];

  /**
   * Tests the "Group column: Entity ID" functionality.
   */
  public function testGroupRows(): void {
    $this->installConfig(['filter']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    NodeType::create([
      'type' => 'page',
      'name' => 'Page',
    ])->save();

    // Create nodes with the same bundle.
    Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ])->save();
    Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ])->save();
    Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ])->save();

    // Tests that by grouping by entity ID we get a row per node.
    $view = Views::getView('test_group_entity_id');
    $this->executeView($view);
    $this->assertCount(3, $view->result);

    // Tests that by grouping by target ID we get a single row.
    $view = Views::getView('test_group_entity_id');
    $view->setHandlerOption('default', 'field', 'type', 'group_column', 'target_id');
    $this->executeView($view);
    $this->assertCount(1, $view->result);
  }

}
