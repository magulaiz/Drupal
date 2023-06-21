<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Hook\SingleModuleCallbackList;

/**
 * List of callbacks for a single module and hook.
 */
interface SingleModuleCallbackListInterface {

  /**
   * Check if the main $module . '_' . $hook function was known at discovery.
   *
   * If not, a new function_exists() check is needed.
   *
   * @return bool
   *   TRUE, if the main function was known at discovery.
   */
  public function containsMainFunction(): bool;

  /**
   * Invokes the callbacks.
   *
   * @param array $args
   *   Arguments.
   *
   * @return mixed
   *   Combined result, using replace and merge rules.
   */
  public function invoke(array $args = []): mixed;

  /**
   * Gets callbacks.
   *
   * @return list<\Closure>
   *   Callbacks.
   */
  public function getCallbacks(): array;

}
