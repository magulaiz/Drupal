<?php

namespace Drupal\Tests\Core\Flood;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Flood\MemoryBackend;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the memory flood implementation.
 *
 * @group flood
 * @coversDefaultClass \Drupal\Core\Flood\MemoryBackend
 */
class MemoryBackendTest extends UnitTestCase {

  /**
   * The tested memory flood backend.
   *
   * @var \Drupal\Core\Flood\MemoryBackend
   */
  protected MemoryBackend $flood;

  /**
   * A test time service.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * A request for testing.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\Request
   */
  protected $testRequest;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $requestStack = $this->createMock(RequestStack::class);
    $this->testRequest = $this->createMock(Request::class);
    $requestStack->expects($this->any())
      ->method('getCurrentRequest')
      ->willReturn($this->testRequest);
    $this->time = $this->createMock(TimeInterface::class);
    $this->flood = new class($requestStack, $this->time) extends MemoryBackend {

      /**
       * Get all flood events.
       *
       * @return array
       *   All flood events.
       */
      final public function getEvents(): array {
        return $this->events;
      }

    };
  }

  /**
   * Tests an allowed flood event with an identifier.
   *
   * @covers ::isAllowed
   */
  public function testAllowed(): void {
    // Ensure IP is not retrieved when an identifier is passed.
    $this->testRequest->expects($this->never())->method('getClientIp');
    $this->time->expects($this->any())
      ->method('getRequestMicroTime')
      ->willReturn(0.0);

    $eventName = 'test_event';
    $identifier = 'test_identifier';
    $threshold = 1;
    $window = 10;

    $this->assertTrue($this->flood->isAllowed($eventName, $threshold, $window, $identifier));
    $this->flood->register($eventName, $window, $identifier);
    // More than the allowed calls ($threshold).
    $this->assertFalse($this->flood->isAllowed($eventName, $threshold, $window, $identifier));
  }

  /**
   * Tests when no identifier is passed.
   *
   * When no identifier is passed, the client IP is used.
   */
  public function testDefaultIdentifier(): void {
    $eventName = 'test_identifier';
    $window = 10;
    $ip = '1.2.3.4';
    $identifier = NULL;

    $this->testRequest->expects($this->exactly(3))
      ->method('getClientIp')
      ->willReturn($ip);
    $this->time->expects($this->any())
      ->method('getRequestMicroTime')
      ->willReturn(0.0);
    $this->flood->register($eventName, $window, $identifier);
    $this->assertCount(1, $this->flood->getEvents()[$eventName][$ip]);
    $this->flood->isAllowed($eventName, $window, $identifier);
    $this->flood->clear($eventName, $identifier);
    $this->assertCount(0, $this->flood->getEvents()[$eventName]);
  }

  /**
   * Tests pre-expired events are accepted.
   *
   * Even if an expired event is registered, isAllowed will still return
   * false until the event is garbage collected.
   *
   * @covers ::isAllowed
   */
  public function testExpiring(): void {
    $this->testRequest->expects($this->never())->method('getClientIp');
    $this->time->expects($this->any())
      ->method('getRequestMicroTime')
      ->willReturn(0.0);

    $eventName = 'test_event_name';
    $identifier = 'test_identifier';
    $window = 10;
    $windowExpired = -1;
    $threshold = 1;

    // Register expired event.
    $this->flood->register($eventName, $windowExpired, $identifier);
    // Verify event is not allowed.
    $this->assertFalse($this->flood->isAllowed($eventName, $threshold, $window, $identifier));
    // "Run cron", which clears the flood data and verify event is now allowed.
    $this->flood->garbageCollection();
    $this->assertTrue($this->flood->isAllowed($eventName, $threshold, $window, $identifier));
  }

  /**
   * Test events are only garbage collected when the current time passes expiry.
   *
   * @covers ::garbageCollection
   */
  public function testGarbageCollection(): void {
    $this->testRequest->expects($this->never())->method('getClientIp');
    $this->time->expects($this->any())
      ->method('getRequestMicroTime')
      ->willReturnOnConsecutiveCalls(0.0, 6.0, 12.0);
    $eventName = 'test_event_name';
    $identifier = 'test_identifier';
    $window = 10;

    $this->assertCount(0, $this->flood->getEvents());
    $this->flood->register($eventName, $window, $identifier);
    $this->assertCount(1, $this->flood->getEvents()[$eventName][$identifier]);

    // Progress time before window, event still exists after garbage collection.
    $this->flood->garbageCollection();
    $this->assertCount(1, $this->flood->getEvents()[$eventName][$identifier]);

    // Progress time after window, event deleted after garbage collection.
    $this->flood->garbageCollection();
    $this->assertCount(0, $this->flood->getEvents()[$eventName][$identifier]);
  }

  /**
   * Tests memory backend records events to the nearest microsecond.
   */
  public function testMemoryBackendThreshold(): void {
    $this->testRequest->expects($this->never())->method('getClientIp');
    $this->time->expects($this->any())
      ->method('getRequestMicroTime')
      ->willReturn(0.0);
    $eventName = 'test_event_name';
    $identifier = 'test_identifier';
    $this->flood->register($eventName, identifier: $identifier);
    $this->assertTrue($this->flood->isAllowed($eventName, 2, identifier: $identifier));
    $this->flood->register($eventName, identifier: $identifier);
    $this->assertFalse($this->flood->isAllowed($eventName, 2, identifier: $identifier));
  }

  /**
   * Tests events for an event name and identifier combination can be voided.
   *
   * @covers ::clear
   */
  public function testClear(): void {
    $this->testRequest->expects($this->never())->method('getClientIp');
    $this->time->expects($this->any())
      ->method('getRequestMicroTime')
      ->willReturn(0.0);
    $eventName = 'test_event_name';
    $identifier = 'test_identifier';
    $window = 10;
    $this->flood->register($eventName, $window, $identifier);
    $this->assertCount(1, $this->flood->getEvents()[$eventName][$identifier]);
    $this->flood->clear($eventName, $identifier);
    $this->assertCount(0, $this->flood->getEvents()[$eventName]);
  }

  /**
   * Tests events with a common identifier prefix are cleared.
   *
   * @covers ::clearByPrefix
   */
  public function testClearByPrefix(): void {
    $this->testRequest->expects($this->never())->method('getClientIp');
    $this->time->expects($this->any())
      ->method('getRequestMicroTime')
      ->willReturn(0.0);

    $eventName = 'test_event_name';
    $identifierPrefix = 'test_identifier';
    $identifier1 = $identifierPrefix . '-' . $this->randomMachineName();
    $identifier2 = $identifierPrefix . '-' . $this->randomMachineName();
    $identifierOther = 'other_test_identifier';
    $window = 10;

    $this->flood->register($eventName, $window, $identifier1);
    $this->flood->register($eventName, $window, $identifier2);
    $this->flood->register($eventName, $window, $identifierOther);
    $this->assertEquals([
      $identifier1,
      $identifier2,
      'other_test_identifier',
    ], array_keys($this->flood->getEvents()[$eventName]));

    $this->flood->clearByPrefix($eventName, $identifierPrefix);
    $this->assertEquals([
      // Only events without the prefix remain.
      'other_test_identifier',
    ], array_keys($this->flood->getEvents()[$eventName]));
  }

}
