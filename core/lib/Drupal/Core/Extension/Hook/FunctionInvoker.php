<?php

namespace Drupal\Core\Extension\Hook;

/**
 * Invokes hooks that are implemented as functions.
 *
 * @see hook_hook_info()
 */
class FunctionInvoker {

  /**
   * @var callable
   *   - $hook_implementation: the name of the function that implements the hook.
   *   - $module: the name of the module implementing the hook.
   *   - $hook: the hook's name.
   */
  protected $invoker;

  /**
   * Creates a new instance.
   *
   * @param callable $invoker
   *   - $hook_implementation: The name of the function that implements the hook.
   *   - $module: The name of the module implementing the hook.
   *   - $hook: The hook's name.
   */
  public function __construct(callable $invoker) {
    $this->invoker = $invoker;
  }

  /**
   * Invokes this invoker.
   *
   * @param string $module
   *   The name of the module implementing the hook.
   * @param string $hook
   *   The hook's name.
   *
   * @return mixed
   */
  public function __invoke($module, $hook) {
    return call_user_func($this->invoker, $module . '_' . $hook, $module, $hook);
  }

}
