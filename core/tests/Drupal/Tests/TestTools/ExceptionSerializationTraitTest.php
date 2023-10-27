<?php

namespace Drupal\Tests\TestTools;

use Drupal\Tests\Traits\ExceptionSerializationTrait;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\Tests\Traits\ExceptionSerializationTrait
 *
 * @group TestTools
 */
class ExceptionSerializationTraitTest extends UnitTestCase {

  /**
   * Proves that tests can have problems with serialized closures.
   */
  public function testWithoutTrait(): void {
    $test = new class() extends TestCase {

      public function testFailWithClosure() {
        (static function (\Closure $f) {
          Assert::assertSame(['x'], ['y']);
        })(fn () => NULL);
      }

    };
    $e = $this->runBadTest($test, 'testFailWithClosure');
    $ee = $this->serializeAndCatch($e);
    $this->assertNotNull($ee, 'Exception should not be serializable');
    $this->assertSame(
      "Exception: Serialization of 'Closure' is not allowed",
      get_class($ee) . ': ' . $ee->getMessage(),
    );
  }

  /**
   * Tests that the trait fixes the problem.
   */
  public function testWithTrait(): void {
    $test = new class() extends TestCase {
      use ExceptionSerializationTrait;

      public function testFailWithClosure() {
        (static function (\Closure $f) {
          Assert::assertSame(['x'], ['y']);
        })(fn () => NULL);
      }

    };
    $e = $this->runBadTest($test, 'testFailWithClosure');
    $ee = $this->serializeAndCatch($e);
    $this->assertNull($ee, 'Exception should be serializable');
  }

  /**
   * Runs a test that is expected to fail, and returns the exception.
   *
   * @param \PHPUnit\Framework\TestCase $test
   *   Test case that will fail
   * @param string $method
   *   Test method to run.
   *
   * @return \Exception
   *   Exception from the failing test.
   */
  private function runBadTest(TestCase $test, string $method): \Exception {
    $test->setName($method);
    try {
      $test->runBare();
      $this->fail('Exception should be thrown from running the test.');
    }
    catch (\Throwable $e) {
      $this->assertInstanceOf(ExpectationFailedException::class, $e);
      return $e;
    }
  }

  /**
   * Attempts to serialize a value, and catches the exception if it fails.
   *
   * @param mixed $value
   *   Value to serialize and unserialize.
   *
   * @return \Throwable|null
   *   The exception on failure, or NULL on success.
   */
  private function serializeAndCatch(mixed $value): ?\Throwable {
    try {
      $serialized = serialize($value);
      unserialize($serialized);
      return NULL;
    }
    catch (\Throwable $e) {
      return $e;
    }
  }

}
