<?php

declare(strict_types = 1);

namespace Drupal\TestTools;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * System to track expected and actual calls for mock objects.
 *
 * Unlike prophecy, this enforces a strict order of calls.
 */
class MockCallQueue {

  /**
   * Expected calls with id, callback and message.
   *
   * @var list<string, callable, ?string>
   */
  private array $expectedCalls = [];

  /**
   * Whether the queue is in replay mode.
   *
   * While in replay mode, no new calls can be queued up.
   * Replay mode stops when the last queued call was consumed.
   *
   * @var bool
   */
  private bool $replayMode = FALSE;

  /**
   * Marks whether this queue has been ended.
   *
   * @var bool
   */
  private bool $ended = FALSE;

  /**
   * Callback on serialize.
   *
   * @return list<string>
   *   Property names to preserve.
   */
  public function __sleep(): array {
    return [];
  }

  /**
   * Wraps a mock object to track call order.
   *
   * @param \PHPUnit\Framework\MockObject\MockObject $mock_object
   *   Mock object.
   *
   * @return \Drupal\TestTools\MockCallQueueObjectWrapper
   *   Wrapper object where expected calls can be queued up.
   */
  public function wrapMockObject(MockObject $mock_object): MockCallQueueObjectWrapper {
    $id = get_class($mock_object) . '#' . spl_object_id($mock_object);
    return new MockCallQueueObjectWrapper($this, $mock_object, $id);
  }

  /**
   * Queues an expected call.
   *
   * @param string $expected_id
   *   Id identifying the expected object and method.
   * @param callable $callback
   *   Callback to produce a return value.
   * @param string|null $message
   *   Message for missing calls.
   */
  public function queue(string $expected_id, callable $callback, string $message = NULL): void {
    if ($this->replayMode) {
      $this->assertEmpty();
      $this->replayMode = FALSE;
    }
    $this->expectedCalls[] = [$expected_id, $callback, $message];
  }

  /**
   * Invokes and consumes the first queued call in the list.
   *
   * @param string $id
   *   Id identifying the expected object and method.
   * @param array $args
   *   Actual arguments.
   *
   * @return mixed
   *   Return value.
   */
  public function call(string $id, array $args): mixed {
    $this->replayMode = TRUE;
    $expected = array_shift($this->expectedCalls);
    if ($expected === NULL) {
      $this->ended = TRUE;
      // Use a bogus comparison, to see the arguments printed out.
      Assert::assertNull($args, "Unexpected call to $id().");
    }
    [$expected_id, $callback] = $expected;
    if ($expected_id !== $id) {
      $this->ended = TRUE;
      // Use a bogus comparison, to see the arguments printed out.
      Assert::assertNull($args, "Unexpected call to $id(), was expecting a call to $expected_id() instead.");
    }
    try {
      return $callback(...$args);
    }
    catch (\Throwable $e) {
      $this->ended = TRUE;
      throw $e;
    }
  }

  /**
   * Destructor.
   *
   * Makes sure that no calls were left over.
   */
  public function __destruct() {
    if (!$this->ended) {
      $this->assertEmpty();
    }
  }

  /**
   * Ends the test.
   *
   * This can be called from ->tearDown(), and is cleaner than relying on the
   * destructor.
   */
  public function end(): void {
    $this->ended = TRUE;
    $this->assertEmpty();
  }

  /**
   * Asserts that no further calls are queued up.
   */
  public function assertEmpty(): void {
    foreach ($this->expectedCalls as [$expected_id, $callback, $message]) {
      if ($message === NULL) {
        Assert::fail("Expected call $expected_id() did not happen.");
      }
      else {
        Assert::fail("Expected call $expected_id() did not happen: $message");
      }
    }
  }

}
