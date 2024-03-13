<?php

/**
 * @file
 * Contains async helper functions for the Drupal framework.
 *
 * Functions in this file are there to provide common actions in async
 * programming.
 */

declare(strict_types=1);

namespace Drupal\Core\Async;

use Drupal\Core\Result;
use Revolt\EventLoop;

/**
 * Stream a set of operations that execute concurrently.
 *
 * Will kick off a set of simultaneous operations and yield whenever an
 * operation has finished.
 *
 * @template KeyT
 * @template ValueT
 *
 * @param array<KeyT, callable() : ValueT> $operations
 *   The set of operations to execute simultaneously.
 *
 * @return \Generator<KeyT, \Drupal\Core\Result<ValueT, \Throwable>>
 *   A generator that will yield the key and value whenever an operation is
 *   completed.
 */
function stream(array $operations) : \Generator {
  /** @var \Revolt\EventLoop\Suspension<array{KeyT, \Drupal\Core\Result<ValueT, \Throwable>}> $suspension */
  $suspension = EventLoop::getSuspension();

  // Kick of all the events that happen.
  foreach ($operations as $key => $operation) {
    EventLoop::defer(function () use ($suspension, $key, $operation) {
      try {
        $result = $operation();
        $suspension->resume([$key, Result::ok($result)]);
      }
      catch (\Throwable $e) {
        $suspension->resume([$key, Result::error($e)]);
      }
    });
  }

  // We must suspend the same number of times that we queued operations to make
  // sure we complete them all.
  for ($i = 0, $j = count($operations); $i < $j; $i++) {
    // We don't need to try/catch here since we ensure errors thrown by the
    // operation itself are caught earlier.
    [$key, $result] = $suspension->suspend();
    yield $key => $result;
  }
}

/**
 * Delay execution in a non-blocking way.
 *
 * The code after this function call will resume after at least $seconds of
 * waiting but the program will remain available to do other tasks in the
 * meantime. The delay is a minimum and approximate, accuracy is not guaranteed.
 *
 * @param int<0,max> $seconds
 *   Delay time in seconds.
 */
function sleepNonBlocking(int $seconds) : void {
  $suspension = EventLoop::getSuspension();
  EventLoop::delay($seconds, fn () => $suspension->resume());
  $suspension->suspend();
}

/**
 * Delay execution in a non-blocking way in microseconds.
 *
 * The code after this function call will resume after at least $seconds of
 * waiting but the program will remain available to do other tasks in the
 * meantime. The delay is a minimum and approximate, accuracy is not guaranteed.
 *
 * @param int<0,max> $microseconds
 *   Delay time in micro seconds. A micro second is one millionth of a second.
 */
function usleepNonBlocking(int $microseconds) : void {
  $suspension = EventLoop::getSuspension();
  EventLoop::delay($microseconds / 1000000, fn () => $suspension->resume());
  $suspension->suspend();
}
