<?php

declare(strict_types=1);

namespace Drupal\Core\Hook;

/**
 * Base class for attributes that affect or define hook implementations.
 *
 * @internal
 */
abstract class HookOperation {

  /**
   * Constructs a HookOperation object.
   *
   * @param string $hook
   *   The hook being implemented or modified.
   * @param string $method
   *   The method for the hook being implemented or modified.
   *   This is required when modifying existing hook implementations it is
   *   optional otherwise. See \Drupal\Core\Hook\Attribute\Hook for more
   *   information.
   * @param class-string $class
   *   (optional) The class of the hook being implemented or modified.
   * @param \Drupal\Core\Hook\Order|\Drupal\Core\Hook\ComplexOrder|null $order
   *   (optional) Set the order of the hook referenced.
   */
  public function __construct(
    public string $hook,
    public string $method,
    public ?string $class = NULL,
    public Order|ComplexOrder|null $order = NULL,
  ) {}

}
