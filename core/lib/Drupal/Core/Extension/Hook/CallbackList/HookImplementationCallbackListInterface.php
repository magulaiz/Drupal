<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Hook\CallbackList;

/**
 * Object containing hook implementation callbacks.
 *
 * @todo Decide which of these methods we want to keep.
 */
interface HookImplementationCallbackListInterface {

  /**
   * Invokes all, and returns values as a list.
   *
   * @param array $args
   *   Arguments.
   *
   * @return list<mixed>
   *   Return values from all implementations.
   */
  public function invokeAllGetList(array $args): array;

  /**
   * Invokes all, and merges the result.
   *
   * @param array $args
   *   Arguments.
   *
   * @return array
   *   Merged results.
   */
  public function invokeAll(array $args): array;

  /**
   * Invokes all and iterates over the values.
   *
   * @param array $args
   *   Arguments.
   *
   * @return \Iterator<int, mixed>
   *   Return value iterator.
   */
  public function invokeAllYieldValue(array $args): \Iterator;

  /**
   * Invokes all and iterates over the values by module.
   *
   * @param array $args
   *   Arguments.
   *
   * @return \Iterator<string, mixed>
   *   Return value iterator, with module names as keys.
   *   Note that module names can occur more than once.
   *   Converting this iterator to array can result in data loss!
   */
  public function invokeAllYieldByModule(array $args): \Iterator;

  /**
   * Invokes all, and ignores the result.
   *
   * This can be faster than other methods because it can skip the results
   * merging.
   *
   * @param array $args
   *   Arguments.
   */
  public function invokeAllVoid(array $args): void;

  /**
   * Invokes all using a callback.
   *
   * @param callable(\Closure, string): void $callback
   *   Callback that will be called for each implementation.
   *   The callback receives two arguments:
   *     - Closure for the hook implementation.
   *     - Machine name of the module that "owns" the implementation.
   */
  public function invokeAllWith(callable $callback): void;

  /**
   * Invokes all to alter a value.
   *
   * @param mixed $data
   *   Value to be altered.
   * @param mixed|null $context1
   *   Additional context.
   * @param mixed|null $context2
   *   Additional context.
   */
  public function invokeAllAlter(mixed &$data, mixed &$context1 = NULL, mixed &$context2 = NULL): void;

}
