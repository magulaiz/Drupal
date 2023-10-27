<?php

namespace Drupal\Tests\TestTools;

use Drupal\TestTools\ExceptionCleaner;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * @coversDefaultClass \Drupal\TestTools\ExceptionCleaner
 *
 * @group TestTools
 */
class ExceptionCleanerTest extends UnitTestCase {

  /**
   * Tests that given exceptions are not serializable.
   *
   * This proves that the exception cleaner is necessary.
   *
   * @param \Throwable $e
   *   Exception or error to check.
   * @param string $expected_message
   *   Expected message.
   *
   * @dataProvider providerTestCleanException
   */
  public function testBadException(\Throwable $e, string $expected_message) {
    $ee = $this->serializeAndCatch($e);
    $this->assertNotNull($ee, 'Exception is expected to not be serializable.');
    $this->assertSame(
      $expected_message,
      get_class($ee) . ': ' . $ee->getMessage(),
    );
  }

  /**
   * Tests that given exceptions are serializable once they are "cleaned up".
   *
   * This proves that the exception cleaner is sufficient.
   *
   * @param \Throwable $e
   *   Exception or error to check.
   *
   * @dataProvider providerTestCleanException
   */
  public function testCleanException(\Throwable $e) {
    $cleaner = new ExceptionCleaner();
    $cleaner->cleanException($e);
    $ee = $this->serializeAndCatch($e);
    $this->assertNull($ee, 'Cleaned-up exception can be serialized and unserialized.');
  }

  /**
   * Data provider.
   *
   * @return \Iterator
   */
  public function providerTestCleanException(): \Iterator {
    yield from (static function (\Closure $f): \Iterator {
      $message = "Exception: Serialization of 'Closure' is not allowed";
      yield 'Exception' => [new \Exception(), $message];
      yield 'Error' => [new \Error(), $message];
      yield 'ComparisonFailure' => [
        new ComparisonFailure(['x'], ['y'], '[x]', '[y]'),
        $message,
      ];
      yield 'ExpectationFailed + ComparisonFailure' => [
        new ExpectationFailedException(
          'Expectation failed',
          new ComparisonFailure(['x'], ['y'], '[x]', '[y]'),
        ),
        $message,
      ];
    })(fn () => NULL);

    yield from (static function (\Reflector $r): \Iterator {
      $message = "Exception: Serialization of 'ReflectionClass' is not allowed";
      yield 'Exception with Reflector' => [new \Exception(), $message];
    })(new \ReflectionClass(\stdClass::class));
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
