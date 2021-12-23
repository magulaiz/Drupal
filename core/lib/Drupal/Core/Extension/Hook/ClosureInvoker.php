<?php

namespace Drupal\Core\Extension\Hook;

/**
 * Allows using a function invoker with a closure instead of a function.
 */
final class ClosureInvoker extends FunctionInvoker {

  /**
   * A function invoker.
   *
   * @var \Drupal\Core\Extension\Hook\FunctionInvoker
   */
  private FunctionInvoker $functionInvoker;

  /**
   * A callable.
   *
   * @var callable
   */
  private $callable;

  /**
   * Constructs a ClosureInvoker.
   *
   * @param \Drupal\Core\Extension\Hook\FunctionInvoker $functionInvoker
   *   A FunctionInvoker instance.
   * @param callable $callable
   *   A closure.
   */
  public function __construct(FunctionInvoker $functionInvoker, callable $callable) {
    $this->functionInvoker = $functionInvoker;
    $this->callable = $callable;
  }

  /**
   * {@inheritdoc}
   */
  public function __invoke($module, $hook) {
    return call_user_func($this->functionInvoker->invoker, $this->callable, $module, $hook);
  }

}
