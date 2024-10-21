<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests node validation constraints.
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

    $this->installSchema('node', ['node_access']);

    // Create a node type for testing.
    $type = NodeType::create(['type' => 'page', 'name' => 'page']);
    $type->save();
  }

  /**
   * Counts the total number of nodes.
   *
   * @return int
   *   Total number of nodes.
   */
  protected function nodeCount(): int {
    $query = \Drupal::entityQuery('node')->accessCheck(FALSE);
    $result = $query->count()->execute();
    return $result;
  }

  /**
   * Tests the node validation constraints.
   */
  public function testDelete(): void {
    $this->createUser();
    $node = Node::create(['type' => 'page', 'title' => 'test', 'uid' => 1]);
    $node->save();
    $this->assertEquals(1, $this->nodeCount(), 'Expect 1 node after creation.');
    $node->delete();
    $this->assertEquals(0, $this->nodeCount(), 'Expect 0 nodes after creation.');
  }
}
