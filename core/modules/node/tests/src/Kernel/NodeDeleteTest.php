<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests node deletion.
 *
 * @group node
 */
class NodeDeleteTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node'];

  /**
   * Set the default field storage backend for fields created during tests.
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a node type for testing.
    $type = NodeType::create(['type' => 'page', 'name' => 'page']);
    $type->save();
  }

  /**
   * Tests the node deletion.
   */
  public function testDelete(): void {
    $this->createUser();
    $node = Node::create(['type' => 'page', 'title' => 'test', 'uid' => 1]);
    $node->save();
    $node->delete();
  }

}
