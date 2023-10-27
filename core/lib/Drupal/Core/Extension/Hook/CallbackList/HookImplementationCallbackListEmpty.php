<?php

namespace Drupal\Core\Extension\Hook\CallbackList;

/**
 * Empty callback list, for a hook with no implementations.
 *
 * This saves time on repeated calls.
 */
class HookImplementationCallbackListEmpty implements HookImplementationCallbackListInterface {

  /**
   * {@inheritdoc}
   */
  public function invokeAllGetList(array $args): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAll(array $args): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAllYieldValue(array $args): \Iterator {
    return new \EmptyIterator();
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAllYieldByModule(array $args): \Iterator {
    return new \EmptyIterator();
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAllVoid(array $args): void {}

  /**
   * {@inheritdoc}
   */
  public function invokeAllWith(callable $callback): void {}

  /**
   * {@inheritdoc}
   */
  public function invokeAllAlter(mixed &$data, mixed &$context1 = NULL, mixed &$context2 = NULL): void {}

}
