<?php

namespace Drupal\Tests\history\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;

/**
 * Tests legacy history module functionality.
 *
 * @group history
 * @group legacy
 */
class HistoryLegacyTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'history'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('history', 'history');
    $user = $this->createUser();
    \Drupal::currentUser()->setAccount($user);
  }

  /**
   * Test legacy history read/write procedural functions.
   */
  public function testLegacyProceduralFunctions() {
    $node = Node::create([
      'type' => 'default',
      'title' => $this->randomMachineName(),
    ]);
    $node->save();

    // 0 is default time for nodes without history.
    $this->assertNull(\Drupal::service('history.repository')->getTime($node));
    $this->assertSame([$node->id() => 0], history_read_multiple([$node->id()]));

    // Assert procedural functions return same results as repository methods.
    history_write($node->id(), $user);
    $this->assertSame(history_read($node->id()), \Drupal::service('history.repository')->getTime($node));
    $this->assertSame(history_read_multiple([$node->id()]), \Drupal::service('history.repository')->getTimes('node', [$node->id()]));
  }

}
