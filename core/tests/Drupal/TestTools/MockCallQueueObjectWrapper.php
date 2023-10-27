<?php

declare(strict_types = 1);

namespace Drupal\TestTools;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use SebastianBergmann\Exporter\Exporter;

/**
 * Helper to track expected and actual calls for a single mock object.
 *
 * @see \Drupal\TestTools\MockCallQueue
 */
class MockCallQueueObjectWrapper {

  /**
   * Map of methods that are already being tracked.
   *
   * @var array<string, true>
   */
  private array $methodsTracked = [];

  /**
   * Constructor.
   *
   * @param \Drupal\TestTools\MockCallQueue $queue
   *   Global call queue.
   * @param \PHPUnit\Framework\MockObject\MockObject $mockObject
   *   Mock object.
   * @param string $id
   *   Id to identify this object in the global queue.
   */
  public function __construct(
    private readonly MockCallQueue $queue,
    private readonly MockObject $mockObject,
    private readonly string $id,
  ) {}

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
   * Starts tracking calls to given methods.
   *
   * @param list<string> $methods
   *   Method names.
   *
   * @return $this
   */
  public function observeMethods(array $methods): static {
    foreach ($methods as $method) {
      $this->observeMethod($method);
    }
    return $this;
  }

  /**
   * Starts tracking calls to a method.
   *
   * @param string $method
   *   Method name.
   *
   * @return $this
   */
  public function observeMethod(string $method): static {
    if (isset($this->methodsTracked[$method])) {
      return $this;
    }
    $method_id = $this->id . '->' . $method;
    $this->mockObject->expects(new AnyInvokedCount())
      ->method($method)
      ->willReturnCallback(
        function (mixed ...$args) use ($method_id) {
          return $this->queue->call($method_id, $args);
        },
      );
    $this->methodsTracked[$method] = TRUE;
    return $this;
  }

  /**
   * Queues an expected method call with no return value.
   *
   * @param string $method
   *   Expected method name.
   * @param array $expected_args
   *   Expected arguments.
   * @param bool $strict
   *   TRUE for strict comparison of arguments.
   *
   * @return $this
   */
  public function queueVoid(string $method, array $expected_args, bool $strict = TRUE): static {
    return $this->queueReturn($method, $expected_args, NULL, $strict);
  }

  /**
   * Queues an expected method call with defined return value.
   *
   * @param string $method
   *   Expected method name.
   * @param array $expected_args
   *   Expected arguments.
   * @param mixed $return
   *   Return value.
   * @param bool $strict
   *   TRUE for strict comparison of arguments.
   *
   * @return $this
   */
  public function queueReturn(string $method, array $expected_args, mixed $return, bool $strict = TRUE): static {
    return $this->queueCallback(
      $method,
      $this->fnAssertArguments($method, $expected_args, $return, $strict),
      'Expected arguments: ' . (new Exporter)->export($expected_args),
    );
  }

  /**
   * Gets a callback to assert arguments.
   *
   * @param string $method
   *   Expected method name.
   * @param array $expected_args
   *   Expected arguments.
   * @param mixed $return
   *   Return value.
   * @param bool $strict
   *   TRUE for strict comparison of arguments.
   *
   * @return \Closure
   */
  private function fnAssertArguments(string $method, array $expected_args, mixed $return, bool $strict): \Closure {
    $rm = new \ReflectionMethod($this->mockObject, $method);
    $default_args = [];
    foreach ($rm->getParameters() as $i => $parameter) {
      if ($parameter->isOptional()) {
        $default_args[$i] = $parameter->getDefaultValue();
      }
    }
    return function (mixed ...$args) use ($expected_args, $default_args, $return, $method, $strict): mixed {
      $unexpected_args = array_diff_key($args, $expected_args);
      $unexpected_args_with_default = array_intersect_key($unexpected_args, $default_args);
      foreach ($unexpected_args_with_default as $i => $arg) {
        if ($arg === $default_args[$i]) {
          unset($args[$i]);
        }
      }
      if (!$strict) {
        $exporter = new ObjectIdInsensitiveExporter();
        $expected_args = $exporter->export($expected_args);
        $args = $exporter->export($args);
      }
      Assert::assertSame(
        $expected_args,
        $args,
        sprintf('Arguments for %s->%s().', $this->id, $method),
      );
      return $return;
    };
  }

  /**
   * Queues an expected method call with a callback to produce the return value.
   *
   * @param string $method
   *   Expected method name.
   * @param callable $callback
   *   Callback that produces the return value.
   * @param string|null $message
   *   Message.
   *
   * @return $this
   */
  public function queueCallback(string $method, callable $callback, string $message = NULL): static {
    $this->queue->queue(
      $this->id . '->' . $method,
      $callback,
      $message,
    );
    return $this;
  }

}
