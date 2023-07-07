<?php

declare(strict_types = 1);

namespace Drupal\Tests\TestTools;

use Drupal\Tests\TestTools\Fixture\BazBooClass;
use Drupal\Tests\TestTools\Fixture\FooBarClass;
use Drupal\TestTools\MockCallQueue;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;

// cspell:ignore bazboo subpatterns

/**
 * @coversDefaultClass \Drupal\TestTools\MockCallQueue
 * @covers \Drupal\TestTools\MockCallQueueObjectWrapper
 *
 * @group TestTools
 */
class MockCallQueueTest extends UnitTestCase {

  /**
   * Tests a sequence of calls that goes exactly as expected.
   */
  public function testGoodSequence(): void {
    $foobar = $this->createMock(FooBarClass::class);
    $bazboo = $this->createMock(BazBooClass::class);
    $queue = new MockCallQueue();
    $foobar_wrapper = $queue->wrapMockObject($foobar)
      ->observeMethods(['foo', 'bar']);
    $bazboo_wrapper = $queue->wrapMockObject($bazboo)
      ->observeMethods(['baz', 'boo']);

    $foobar_wrapper->queueReturn('foo', ['X', 'Y'], 'ret');
    $bazboo_wrapper->queueVoid('baz', ['A', 'B']);

    $this->assertSame('ret', $foobar->foo('X', 'Y'));
    $this->assertNull($bazboo->baz('A', 'B'));

    $queue->assertEmpty();

    $foobar_wrapper->queueCallback('bar', function (...$args) {
      $this->assertSame([], $args);
      return 'ret1';
    });

    $bazboo_wrapper->queueCallback('baz', function (...$args) {
      $this->assertSame(['u', 'v'], $args);
      return 'ret2';
    });

    $this->assertSame('ret1', $foobar->bar());
    $this->assertSame('ret2', $bazboo->baz('u', 'v'));

    $queue->end();
  }

  /**
   * Tests optional arguments with default value.
   */
  public function testOptionalArgument(): void {
    $foobar = $this->createMock(FooBarClass::class);
    $queue = new MockCallQueue();
    $foobar_wrapper = $queue->wrapMockObject($foobar)
      ->observeMethod('optionalArgs');

    $foobar_wrapper->queueVoid('optionalArgs', ['X']);
    $foobar->optionalArgs('X');
    $queue->assertEmpty();

    $foobar_wrapper->queueVoid('optionalArgs', ['X', 5]);
    $foobar->optionalArgs('X');
    $queue->assertEmpty();

    $foobar_wrapper->queueVoid('optionalArgs', ['X']);
    $foobar->optionalArgs('X', 5);
    $queue->assertEmpty();

    $foobar_wrapper->queueVoid('optionalArgs', ['X', 5]);
    $foobar->optionalArgs('X', 5);
    $queue->assertEmpty();

    $foobar_wrapper->queueVoid('optionalArgs', ['X', 7]);
    $foobar->optionalArgs('X', 7);
    $queue->assertEmpty();

    $foobar_wrapper->queueVoid('optionalArgs', ['X']);
    $this->assertException(
      $this->callAndCatch(fn() => $foobar->optionalArgs('X', 7)),
      ExpectationFailedException::class,
      'Arguments for Mock_FooBarClass_%s->optionalArgs()',
      ['.*'],
    );
    $queue->assertEmpty();

    $foobar_wrapper->queueVoid('optionalArgs', ['X', 7]);
    $this->assertException(
      $this->callAndCatch(fn() => $foobar->optionalArgs('X')),
      ExpectationFailedException::class,
      'Arguments for Mock_FooBarClass_%s->optionalArgs()',
      ['.*'],
    );
    $queue->assertEmpty();
  }

  /**
   * Tests a sequence of calls that goes in the wrong order.
   */
  public function testBadOrder(): void {
    $foobar = $this->createMock(FooBarClass::class);
    $bazboo = $this->createMock(BazBooClass::class);
    $queue = new MockCallQueue();
    $foobar_wrapper = $queue->wrapMockObject($foobar)
      ->observeMethods(['foo', 'bar']);
    $bazboo_wrapper = $queue->wrapMockObject($bazboo)
      ->observeMethods(['baz', 'boo']);

    $foobar_wrapper->queueReturn('foo', ['X', 'Y'], 'ret');
    $bazboo_wrapper->queueVoid('baz', ['A', 'B']);

    $this->assertException(
      $this->callAndCatch(fn () => $bazboo->baz('A', 'B')),
      ExpectationFailedException::class,
      'Unexpected call to Mock_BazBooClass%s->baz(), was expecting a call to Mock_FooBarClass%s->foo() instead.%s',
      ['.*', '.*', '[.\n]*'],
    );
  }

  /**
   * Tests a call with wrong arguments.
   */
  public function testBadArguments(): void {
    $foobar = $this->createMock(FooBarClass::class);
    $queue = new MockCallQueue();
    $foobar_wrapper = $queue->wrapMockObject($foobar)
      ->observeMethods(['foo', 'bar']);

    $foobar_wrapper->queueReturn('foo', ['X', 'Y'], 'ret');

    $this->assertException(
      $this->callAndCatch(fn () => $foobar->foo('A', 'B')),
      ExpectationFailedException::class,
      'Arguments for Mock_FooBarClass_%s->foo().',
      ['.*'],
    );
  }

  /**
   * Tests an expected call that is never called.
   */
  public function testExpectedNeverCalled(): void {
    $foobar = $this->createMock(FooBarClass::class);
    $queue = new MockCallQueue();
    $foobar_wrapper = $queue->wrapMockObject($foobar)
      ->observeMethods(['foo', 'bar']);

    $foobar_wrapper->queueReturn('foo', ['X', 'Y'], 'ret');
    $foobar_wrapper->queueReturn('foo', ['X1', 'Y1'], 'ret1');

    $foobar->foo('X', 'Y');

    $this->assertException(
      $this->callAndCatch($queue->end(...)),
      AssertionFailedError::class,
      'Expected call Mock_FooBarClass_%s->foo() did not happen',
      ['.*']
    );
  }

  /**
   * Tests an unexpected call being made.
   */
  public function testCallNotExpected(): void {
    $foobar = $this->createMock(FooBarClass::class);
    $queue = new MockCallQueue();
    $foobar_wrapper = $queue->wrapMockObject($foobar)
      ->observeMethods(['foo', 'bar']);

    $foobar_wrapper->queueReturn('foo', ['X', 'Y'], 'ret');

    $foobar->foo('X', 'Y');

    $this->assertException(
      $this->callAndCatch(fn () => $foobar->foo('X1', 'Y1')),
      ExpectationFailedException::class,
      'Unexpected call to Mock_FooBarClass%s->foo()',
      ['.*']
    );
  }

  /**
   * Calls a callback, and catches the exception.
   *
   * @param \Closure $callback
   *   Callback.
   *
   * @return \Throwable|null
   *   Caught exception, or NULL on success.
   */
  private function callAndCatch(\Closure $callback): ?\Throwable {
    try {
      $callback();
      return NULL;
    }
    catch (\Throwable $e) {
      return $e;
    }
  }

  /**
   * Asserts an exception class and message.
   *
   * @param \Throwable|null $e
   *   Exception or NULL if none was thrown.
   * @param class-string $class
   *   Expected exception class.
   * @param string $message
   *   Expected exception message.
   * @param list<string> $subpatterns
   *   Regex subpatterns to insert where $message has '%s' placeholders.
   */
  private function assertException(?\Throwable $e, string $class, string $message, array $subpatterns = []) {
    if ($e === NULL) {
      $this->fail('Expected exception was not thrown: ' . $class . ': ' . $message);
    }
    $this->assertStringWithSubpatterns(
      $class . ': ' . $message,
      get_class($e) . ': ' . $e->getMessage(),
      $subpatterns,
    );
  }

  /**
   * Asserts a string match with subpatterns.
   *
   * @param string $expected
   *   Expected string with '%s' placeholders.
   * @param string $actual
   *   Actual string.
   * @param array $subpatterns
   *   Regex subpatterns to insert into the placeholders.
   *   The assumed regex delimiter is '@'.
   */
  private function assertStringWithSubpatterns(string $expected, string $actual, array $subpatterns): void {
    if ($subpatterns) {
      $pattern = '@^' . vsprintf(preg_quote($expected, '@'), $subpatterns) . '@';
      $this->assertMatchesRegularExpression($pattern, $actual, sprintf("Expected: %s\n  Actual: %s\nSubpatterns: %s\n", $expected, $actual, implode(', ', $subpatterns)));
    }
    else {
      $this->assertSame($expected, $actual);
    }
  }

}
