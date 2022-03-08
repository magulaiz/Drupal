<?php

namespace Drupal\Tests\history\Kernel;

use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\user\Entity\User;

/**
 * Tests the history repository service.
 *
 * @group history
 * @see \Drupal\history\HistoryRepository
 */
class HistoryRepositoryTest extends EntityKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['history', 'node'];

  /**
   * The current user entity.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('history', ['history']);
    $this->installSchema('node', ['node_access']);

    $user = $this->createUser();
    $this->currentUser = $user;
    \Drupal::currentUser()->setAccount($user);
  }

  /**
   * Tests getting and setting times.
   */
  public function testSetGetTime() {
    $node = Node::create([
      'title' => 'n1',
      'type' => 'default',
    ]);
    $node->save();

    // Don't specify current user when setting.
    $time = $this->randomTimestamp();
    \Drupal::service('history.repository')->setTime($node, NULL, $time);
    $this->assertSame($time, \Drupal::service('history.repository')->getTime($node));
    $this->assertSame($time, \Drupal::service('history.repository')->getTime($node, $this->currentUser));
    \Drupal::service('history.repository')->resetCache();
    $this->assertSame($time, \Drupal::service('history.repository')->getTime($node, $this->currentUser));

    // Specify current user when setting.
    $time = $this->randomTimestamp();
    \Drupal::service('history.repository')->setTime($node, $this->currentUser, $time);
    $this->assertSame($time, \Drupal::service('history.repository')->getTime($node));
    $this->assertSame($time, \Drupal::service('history.repository')->getTime($node, $this->currentUser));
    \Drupal::service('history.repository')->resetCache();
    $this->assertSame($time, \Drupal::service('history.repository')->getTime($node, $this->currentUser));

    // Specify non-current user when setting.
    $notCurrentUser = User::create(['name' => 'notCurrent']);
    $notCurrentUser->save();
    $time = $this->randomTimestamp();
    \Drupal::service('history.repository')->setTime($node, $notCurrentUser, $time);
    $this->assertNotSame($time, \Drupal::service('history.repository')->getTime($node));
    $this->assertSame($time, \Drupal::service('history.repository')->getTime($node, $notCurrentUser));
    \Drupal::service('history.repository')->resetCache();
    $this->assertSame($time, \Drupal::service('history.repository')->getTime($node, $notCurrentUser));

    // Set & get multiple.
    $node2 = Node::create([
      'title' => 'n2',
      'type' => 'default',
    ]);
    $node2->save();
    $nids = [$node->id(), $node2->id()];
    $time = $this->randomTimestamp();
    \Drupal::service('history.repository')->setTimes('node', $nids, NULL, $time);
    $times = [$node->id() => $time, $node2->id() => $time];
    $this->assertEqualsCanonicalizing($times, \Drupal::service('history.repository')->getTimes('node', $nids));
    $this->assertEqualsCanonicalizing($times, \Drupal::service('history.repository')->getTimes('node', $nids, $this->currentUser));
    \Drupal::service('history.repository')->resetCache();
    $this->assertEqualsCanonicalizing($times, \Drupal::service('history.repository')->getTimes('node', $nids, $this->currentUser));

    // Get some from cache and some from database.
    $time = $this->randomTimestamp();
    \Drupal::service('history.repository')->setTimes('node', $nids, NULL, $time);
    \Drupal::service('history.repository')->resetCache();
    $times = [$node->id() => $time, $node2->id() => $time];
    $this->assertEqualsCanonicalizing($times, \Drupal::service('history.repository')->getTimes('node', $nids));
    \Drupal::service('history.repository')->resetCache('node', [$node2->id()]);
    $this->assertEqualsCanonicalizing($times, \Drupal::service('history.repository')->getTimes('node', $nids));

  }

  /**
   * Tests default on getTime, with and without caching.
   */
  public function testDefaultGetTime() {
    $node = Node::create([
      'title' => 'n1',
      'type' => 'default',
    ]);
    $node->save();
    // First call to getTime() tries the database and caches the result.
    $this->assertSame(NULL, \Drupal::service('history.repository')->getTime($node, $this->currentUser, NULL));
    $this->assertSame(NULL, \Drupal::service('history.repository')->getTime($node, $this->currentUser, NULL));
    \Drupal::service('history.repository')->resetCache();
    $this->assertSame(FALSE, \Drupal::service('history.repository')->getTime($node, $this->currentUser, FALSE));
    $this->assertSame(FALSE, \Drupal::service('history.repository')->getTime($node, $this->currentUser, FALSE));
    \Drupal::service('history.repository')->resetCache();
    $this->assertSame(0, \Drupal::service('history.repository')->getTime($node, $this->currentUser, 0));
    $this->assertSame(0, \Drupal::service('history.repository')->getTime($node, $this->currentUser, 0));
    \Drupal::service('history.repository')->resetCache();
    $this->assertSame(1000, \Drupal::service('history.repository')->getTime($node, $this->currentUser, 1000));
    $this->assertSame(1000, \Drupal::service('history.repository')->getTime($node, $this->currentUser, 1000));
    \Drupal::service('history.repository')->resetCache();
    $this->assertSame('', \Drupal::service('history.repository')->getTime($node, $this->currentUser, ''));
    $this->assertSame('', \Drupal::service('history.repository')->getTime($node, $this->currentUser, ''));
    \Drupal::service('history.repository')->resetCache();
    $this->assertSame('missing', \Drupal::service('history.repository')->getTime($node, $this->currentUser, 'missing'));
    $this->assertSame('missing', \Drupal::service('history.repository')->getTime($node, $this->currentUser, 'missing'));
  }

  /**
   * Tests default on getTimes.
   */
  public function testDefaultGetTimes() {
    $node1 = Node::create([
      'title' => 'n1',
      'type' => 'default',
    ]);
    $node1->save();
    $node2 = Node::create([
      'title' => 'n2',
      'type' => 'default',
    ]);
    $node2->save();

    // Exclude missing nodes if null is default.
    $this->assertSame([], \Drupal::service('history.repository')->getTimes('node', [$node1->id()], $this->currentUser, NULL));
    // Cached result should be same.
    $this->assertSame([], \Drupal::service('history.repository')->getTimes('node', [$node1->id()], $this->currentUser, NULL));

    // Exclude missing nodes if 0 is default.
    \Drupal::service('history.repository')->resetCache();
    $this->assertSame([$node1->id() => 0], \Drupal::service('history.repository')->getTimes('node', [$node1->id()], $this->currentUser, 0));
    // Cached result should be same.
    $this->assertSame([$node1->id() => 0], \Drupal::service('history.repository')->getTimes('node', [$node1->id()], $this->currentUser, 0));

    // Exclude missing nodes if FALSE is default.
    \Drupal::service('history.repository')->resetCache();
    $this->assertSame([$node1->id() => FALSE], \Drupal::service('history.repository')->getTimes('node', [$node1->id()], $this->currentUser, FALSE));
    // Cached result should be same.
    $this->assertSame([$node1->id() => FALSE], \Drupal::service('history.repository')->getTimes('node', [$node1->id()], $this->currentUser, FALSE));
  }

  /**
   * Tests the cache.
   */
  public function testCache() {
    $node = Node::create([
      'title' => 'n1',
      'type' => 'default',
    ]);
    $node->save();
    $old = $this->randomTimestamp();

    // Missing times are cached.
    \Drupal::service('history.repository')->getTime($node, $this->currentUser, $old);

    // Manipulate database directly so cache is invalid.
    $new = $old + 10;
    $connection = Database::getConnection();
    $connection->insert('history')
      ->fields([
        'uid' => $this->currentUser->id(),
        'nid' => $node->id(),
        'timestamp' => $new,
      ])->execute();

    // Cache is now stale.
    $this->assertNotSame($new, \Drupal::service('history.repository')->getTime($node, $this->currentUser));

    // Reset irrelevant parts of cache, cache still stale.
    $notCurrentUser = $this->createUser();
    \Drupal::service('history.repository')->resetCache('node', [], $notCurrentUser);
    \Drupal::service('history.repository')->resetCache('node', [$node->id() + 1]);
    \Drupal::service('history.repository')->resetCache('user');
    $this->assertNotSame($new, \Drupal::service('history.repository')->getTime($node, $this->currentUser));

    // Resetting relevant cache gives results that are no longer stale.
    \Drupal::service('history.repository')->resetCache('node', [$node->id()], $this->currentUser);
    $this->assertSame($new, \Drupal::service('history.repository')->getTime($node, $this->currentUser));
  }

  /**
   * Tests deleting history.
   */
  public function testDeleteEntity() {
    $node = Node::create([
      'title' => 'n1',
      'type' => 'default',
    ]);
    $node->save();
    $nid = $node->id();
    \Drupal::service('history.repository')->setTime($node);
    $node->delete();
    $deletedHistory = \Drupal::service('history.repository')->getTimes('node', [$nid]);
    $this->assertSame([], $deletedHistory);
  }

  /**
   * Tests deleting history.
   */
  public function testDeleteUser() {
    $node = Node::create([
      'title' => 'n1',
      'type' => 'default',
    ]);
    $node->save();
    $nid = $node->id();
    \Drupal::service('history.repository')->setTime($node, $this->currentUser);
    $this->currentUser->delete();
    $deletedHistory = \Drupal::service('history.repository')->getTimes('node', [$nid], $this->currentUser);
    $this->assertSame([], $deletedHistory);
  }

  /**
   * Tests purge.
   */
  public function testPurge() {
    $node1 = Node::create([
      'title' => 'n1',
      'type' => 'default',
    ]);
    $node1->save();
    $node2 = Node::create([
      'title' => 'n2',
      'type' => 'default',
    ]);
    $node2->save();

    // View node 1 one year ago.
    $yearAgo = \Drupal::time()->getRequestTime() - (86400 * 365);
    \Drupal::service('history.repository')->setTime($node1, $this->currentUser, $yearAgo);

    // View node 2 one week ago.
    $weekAgo = \Drupal::time()->getRequestTime() - (86400 * 7);
    \Drupal::service('history.repository')->setTime($node2, $this->currentUser, $weekAgo);

    // Purge history, defaults to a month ago.
    \Drupal::service('history.repository')->purge();
    // Node 1 from a year ago is gone but node 2 from a week ago is there.
    $remainingHistory = \Drupal::service('history.repository')->getTimes('node', [$node1->id(), $node2->id()]);
    $this->assertSame([$node2->id() => $weekAgo], $remainingHistory);

    // Purge history with explicit time.
    // Node 2 from a week will survive a purge of history from before 14 days ago.
    $fortnightAgo = \Drupal::time()->getRequestTime() - (86400 * 14);
    \Drupal::service('history.repository')->purge($fortnightAgo);
    // Node 2 from a week ago is still there.
    $remainingHistory = \Drupal::service('history.repository')->getTimes('node', [$node1->id(), $node2->id()]);
    $this->assertSame([$node2->id() => $weekAgo], $remainingHistory);
    // Node 2 from a week ago will not survive a purge of history from before a day ago.
    $dayAgo = \Drupal::time()->getRequestTime() - (86400 * 1);
    \Drupal::service('history.repository')->purge($dayAgo);
    $remainingHistory = \Drupal::service('history.repository')->getTimes('node', [$node1->id(), $node2->id()]);
    $this->assertSame([], $remainingHistory);
  }

  /**
   * Tests setting history for non-node entity type.
   *
   * @doesNotPerformAssertions
   */
  public function testSetNonNodeEntityType() {
    $this->expectException(\InvalidArgumentException::class);
    \Drupal::service('history.repository')->setTimes('user', [1]);
  }

  /**
   * Tests getting history for non-node entity type.
   *
   * @doesNotPerformAssertions
   */
  public function testGetNonNodeEntityType() {
    $this->expectException(\InvalidArgumentException::class);
    \Drupal::service('history.repository')->getTimes('user', [1]);
  }

  /**
   * Generate a random timestamp in the last 30 days.
   */
  protected function randomTimestamp() {
    return \Drupal::time()->getRequestTime() - mt_rand(0, 86400 * 30);
  }

}
