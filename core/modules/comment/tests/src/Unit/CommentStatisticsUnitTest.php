<?php

namespace Drupal\Tests\comment\Unit;

use Drupal\comment\CommentStatistics;
use Drupal\Tests\UnitTestCase;
use Drupal\user\EntityOwnerInterface;

// cspell:ignore testcomment

/**
 * @coversDefaultClass \Drupal\comment\CommentStatistics
 * @group comment
 */
class CommentStatisticsUnitTest extends UnitTestCase {

  /**
   * Mock statement.
   *
   * @var \Drupal\Core\Database\StatementInterface
   */
  protected $statement;

  /**
   * Mock select interface.
   *
   * @var \Drupal\Core\Database\Query\SelectInterface
   */
  protected $select;

  /**
   * Mock insert object.
   *
   * @var \Drupal\Core\Database\Query\Insert
   */
  protected $insert;

  /**
   * Mock database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Mock account interface for the user calling CommentStatistics methods.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * User ID returned by the mock user object. Should be set by tests.
   *
   * @var int
   */
  protected $currentUserId;

  /**
   * Values to be added to the mock database row store.
   *
   * @var array
   */
  protected $stagedInsertValues;

  /**
   * Mock database row store for comment_statistics table.
   *
   * @var array
   */
  protected $mockDatabaseStorage;

  /**
   * Mock entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Mock state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * CommentStatistics service under test.
   *
   * @var \Drupal\comment\CommentStatisticsInterface
   */
  protected $commentStatistics;

  /**
   * Counts calls to fetchAssoc().
   *
   * @var int
   */
  protected $callsToFetch;

  /**
   * Sets up required mocks and the CommentStatistics service under test.
   */
  protected function setUp(): void {

    $this->database = $this->getMockBuilder('Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();

    // Set up mock objects for database select.
    $this->statement = $this->getMockBuilder('Drupal\sqlite\Driver\Database\sqlite\Statement')
      ->disableOriginalConstructor()
      ->getMock();

    $this->statement->expects($this->any())
      ->method('fetchObject')
      ->willReturnCallback([$this, 'fetchObjectCallback']);

    $this->select = $this->getMockBuilder('Drupal\Core\Database\Query\Select')
      ->disableOriginalConstructor()
      ->getMock();

    $this->select->expects($this->any())
      ->method('fields')
      ->will($this->returnSelf());

    $this->select->expects($this->any())
      ->method('condition')
      ->will($this->returnSelf());

    $this->select->expects($this->any())
      ->method('execute')
      ->willReturn($this->statement);

    $this->database->expects($this->any())
      ->method('select')
      ->will($this->returnValue($this->select));

    // Set up mock objects for database insert.
    $this->insert = $this->getMockBuilder('Drupal\Core\Database\Query\Insert')
      ->disableOriginalConstructor()
      ->getMock();

    $this->insert->expects($this->any())
      ->method('fields')
      ->will($this->returnSelf());

    $this->insert->expects($this->any())
      ->method('values')
      ->will($this->returnCallback([$this, 'stageValuesCallback']));

    $this->insert->expects($this->any())
      ->method('execute')
      ->will($this->returnCallback([$this, 'updateValuesCallback']));

    $this->database->expects($this->any())
      ->method('insert')
      ->will($this->returnValue($this->insert));

    // Set up mock account which can be told what ID it has.
    $this->currentUser = $this->getMockBuilder('Drupal\Core\Session\AccountInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $this->currentUser->expects($this->any())
      ->method('id')
      ->will($this->returnCallback([$this, 'currentUserIdCallback']));

    $this->entityTypeManager = $this->getMockBuilder('Drupal\Core\Entity\EntityTypeManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->state = $this->getMockBuilder('Drupal\Core\State\StateInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->time = $this->getMockBuilder('Drupal\Component\Datetime\TimeInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->commentStatistics = new CommentStatistics($this->database, $this->currentUser, $this->entityTypeManager, $this->state, NULL, $this->time);
  }

  /**
   * Tests the read method.
   *
   * @see \Drupal\comment\CommentStatistics::read()
   *
   * @group Drupal
   * @group Comment
   */
  public function testRead() {
    $this->callsToFetch = 0;
    $results = $this->commentStatistics->read(['1' => 'boo', '2' => 'foo'], 'snafus');
    $this->assertEquals(['something', 'something-else'], $results);
  }

  /**
   * Tests the create method, by a mock class with EntityOwnerInterface.
   *
   * @see \Drupal\comment\CommentStatistics::create()
   *
   * @group Drupal
   * @group Comment
   */
  public function testCreateEntityWithOwnerInterface() {
    // Test creating with different user IDs: 0, 1, 2.
    // The entity IDs are random. We increment them but probably don't need to.
    $entity_id = 233;
    foreach ([0, 1, 2] as $uid) {
      $entity = $this->getMockBuilder('Drupal\node\Entity\Node')
        ->disableOriginalConstructor()
        ->getMock();

      if ($this->createEntity($entity, 'node', ++$entity_id, $uid)) {
        // Statistics should get the entity's owner id as last_comment_uid.
        $this->assertEquals($uid, $this->mockDatabaseStorage["node:$entity_id:field_testcomment"]['last_comment_uid']);
      }
    }
  }

  /**
   * Tests the create method, by a mock class without EntityOwnerInterface.
   *
   * @see \Drupal\comment\CommentStatistics::create()
   *
   * @group Drupal
   * @group Comment
   */
  public function testCreateEntityWithoutOwnerInterface() {
    // Initialize 'current user' value to use while inserting statistics record.
    $this->currentUserId = 5;
    // Test creating with different user IDs: 0, 1, 2.
    // The entity IDs are random. We increment them but probably don't need to.
    $entity_id = 344;
    foreach ([0, 1, 2] as $uid) {
      $entity = $this->getMockBuilder('Drupal\user\UserInterface')
        ->disableOriginalConstructor()
        ->getMock();

      if ($this->createEntity($entity, 'user', ++$entity_id, $uid)) {
        // Statistics should get current user id as last_comment_uid.
        $this->assertEquals(5, $this->mockDatabaseStorage["user:$entity_id:field_testcomment"]['last_comment_uid']);
      }
    }
  }

  /**
   * Inserts a mock statistics row for an entity.
   *
   * @param object $entity
   *   Entity mock object.
   * @param string $type
   *   Entity type.
   * @param int $id
   *   Entity ID.
   * @param int $uid
   *   Entity owner ID.
   *
   * @return bool
   *   TRUE if no tests failed inside this method.
   *
   * @see \Drupal\comment\CommentStatistics::create()
   *
   * @group Drupal
   * @group Comment
   */
  protected function createEntity($entity, $type, $id, $uid) {

    // Assign various values. OwnerID is for the test; others are unimportant.
    $entity->expects($this->any())
      ->method('getEntityTypeId')
      ->will($this->returnValue($type));

    if ($entity instanceof EntityOwnerInterface) {
      $entity->expects($this->any())
        ->method('getOwnerId')
        ->will($this->returnValue($uid));
    }

    $entity->expects($this->any())
      ->method('id')
      ->will($this->returnValue($id));

    $entity->expects($this->any())
      ->method('getChangedTime')
      ->will($this->returnValue(time()));

    // Every field we pass to create, will 'be present in $entity'.
    $entity->expects($this->any())
      ->method('hasField')
      ->will($this->returnValue(TRUE));

    // We assume $fields values are not used; the create() call signature is
    // like it is, only because it mirrors hook_entity_storage_load().
    $fields = ['field_testcomment' => NULL];
    $this->commentStatistics->create($entity, $fields);

    if (!isset($this->mockDatabaseStorage["$type:$id:field_testcomment"]['last_comment_uid'])) {
      $this->fail("No statistics record or last comment ID was stored for $type:$id:field_testcomment.");
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Return value callback for fetchObject() function on mocked object.
   *
   * @return bool|string
   *   'Something' on first, 'something-else' on second and FALSE for the
   *   other calls to function.
   */
  public function fetchObjectCallback() {
    $this->callsToFetch++;
    switch ($this->callsToFetch) {
      case 1:
        return 'something';

      case 2:
        return 'something-else';

      default:
        return FALSE;
    }
  }

  /**
   * Return value callback for values() function on mocked Insert.
   *
   * @return \Drupal\Core\Database\Query\Insert
   *   The mocked Insert object on which values() was called.
   */
  public function stageValuesCallback($values) {
    $this->stagedInsertValues = $values;
    // Return 'self'.
    return $this->insert;
  }

  /**
   * Return value callback for execute() function on mocked Insert object.
   *
   * @return int
   *   Zero. (An invalid 'insert id'. It's expected not to be used.)
   */
  public function updateValuesCallback() {
    // This method just assumes it's always being called immediately after
    // another 'stage values' callback, that all required values are present,
    // and that an insert won't try to set duplicate rows.
    $vals = $this->stagedInsertValues;
    $pkey = "$vals[entity_type]:$vals[entity_id]:$vals[field_name]";
    $this->mockDatabaseStorage[$pkey] = $vals;
    return 0;
  }

  /**
   * Return value callback for id() function on mocked User.
   *
   * @return int
   *   A user ID, which should be set beforehand by a test method as needed.
   */
  public function currentUserIdCallback() {
    return isset($this->currentUserId) ? $this->currentUserId : 999;
  }

}
