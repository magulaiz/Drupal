<?php

declare(strict_types=1);

namespace Drupal\Core\Hook;

/**
 * Base class for attributes that affect other hook implementations.
 *
 * @internal
 */
abstract class HookOperation {

  /**
   * Constructs a HookOperation object.
   *
   * @param string $hook
   *   The hook parameter of the implementation.
   * @param string $method
   *   The method name of the implementation. If the hook attribute is
   *   on a class and does not have method set, then use __invoke.
   * @param class-string $class
   *   (optional) The class the implementation to modify is in.
   * @param string|null $module
   *   (optional) The module this implementation is for. This allows one module
   *   to implement a hook on behalf of another module. Defaults to the module
   *   the implementation is in.
   * @param \Drupal\Core\Hook\Order|\Drupal\Core\Hook\ComplexOrder|null $order
   *   (optional) Set the order of the implementation.
   */
  public function __construct(
    public string $hook,
    public string $method,
    public ?string $class = '',
    public ?string $module = NULL,
    public Order|ComplexOrder|null $order = NULL,
  ) {}

}
