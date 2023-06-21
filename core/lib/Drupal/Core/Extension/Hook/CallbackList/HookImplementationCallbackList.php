<?php

namespace Drupal\Core\Extension\Hook\CallbackList;

use Drupal\Component\Utility\NestedArray;

/**
 * Object containing hook implementation callbacks.
 */
class HookImplementationCallbackList implements HookImplementationCallbackListInterface {

  /**
   * Constructor.
   *
   * @param list<\Closure> $implementingCallbacks
   *   Implementing callbacks.
   * @param list<string> $modules
   *   Modules, by the same indices as the callbacks.
   */
  public function __construct(
    private readonly array $implementingCallbacks,
    private readonly array $modules,
  ) {
    assert(array_is_list($this->implementingCallbacks));
    assert(array_keys($this->implementingCallbacks) === array_keys($modules));
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAllGetList(array $args): array {
    $results = [];
    foreach ($this->implementingCallbacks as $implementing_callback) {
      $results[] = $implementing_callback(...$args);
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAll(array $args): array {
    $results = [];
    foreach ($this->implementingCallbacks as $implementing_callback) {
      $result = $implementing_callback(...$args);
      if ($result === NULL) {
        continue;
      }
      if (is_array($result)) {
        $results[] = $result;
      }
      else {
        $results[] = [$result];
      }
    }
    return NestedArray::mergeDeepArray($results);
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAllYieldValue(array $args): \Iterator {
    foreach ($this->implementingCallbacks as $implementing_callback) {
      yield $implementing_callback(...$args);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAllYieldByModule(array $args): \Iterator {
    foreach ($this->implementingCallbacks as $module_or_key => $implementing_callback) {
      yield $this->modules[$module_or_key] => $implementing_callback(...$args);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAllVoid(array $args): void {
    foreach ($this->implementingCallbacks as $implementing_callback) {
      $implementing_callback(...$args);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAllWith(callable $callback): void {
    foreach ($this->implementingCallbacks as $module_or_key => $implementing_callback) {
      $callback($implementing_callback, $this->modules[$module_or_key]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAllAlter(mixed &$data, mixed &$context1 = NULL, mixed &$context2 = NULL): void {
    foreach ($this->implementingCallbacks as $implementing_callback) {
      $implementing_callback($data, $context1, $context2);
    }
  }

}
