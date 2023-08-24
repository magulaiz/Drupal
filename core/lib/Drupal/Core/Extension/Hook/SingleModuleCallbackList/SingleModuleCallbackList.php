<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Hook\SingleModuleCallbackList;

use Drupal\Component\Utility\NestedArray;

/**
 * Flexible implementation with any number of implementations.
 */
class SingleModuleCallbackList implements SingleModuleCallbackListInterface {

  /**
   * Constructor.
   *
   * @param list<\Closure> $callbacks
   *   The callbacks.
   * @param bool $containsMainFunction
   *   TRUE if the main $module . '_' . $hook function existed at discovery
   *   time. This can be compared later to see if the list is up to date.
   */
  public function __construct(
    private readonly array $callbacks,
    private readonly bool $containsMainFunction,
  ) {
    assert((static fn (\Closure ...$args) => TRUE)(...$callbacks));
    assert(array_is_list($callbacks));
  }

  /**
   * {@inheritdoc}
   */
  public function containsMainFunction(): bool {
    return $this->containsMainFunction;
  }

  /**
   * {@inheritdoc}
   */
  public function invoke(array $args = []): mixed {
    $return = NULL;
    foreach ($this->callbacks as $callback) {
      $result = $callback(...$args);
      if (is_array($result) && is_array($return)) {
        $return = NestedArray::mergeDeep($return, $result);
      }
      elseif ($result !== NULL) {
        // Replace instead of merging.
        $return = $result;
      }
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function getCallbacks(): array {
    return $this->callbacks;
  }

}
