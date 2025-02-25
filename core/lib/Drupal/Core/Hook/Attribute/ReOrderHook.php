<?php

declare(strict_types=1);

namespace Drupal\Core\Hook\Attribute;

use Drupal\Core\Hook\ComplexOrder;
use Drupal\Core\Hook\HookOperation;
use Drupal\Core\Hook\Order;

/**
 * Set the order of an already existing implementation.
 *
 * @internal
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ReOrderHook extends HookOperation {

  /**
   * Constructs a ReOrderHook object.
   *
   * @param string $hook
   *   The hook parameter of the #Hook being modified.
   * @param class-string $class
   *   The class the implementation to modify is in.
   * @param string $method
   *   The method name of the #Hook being modified. If the hook attribute is
   *   on a class and does not have method set, then use __invoke.
   * @param \Drupal\Core\Hook\Order|\Drupal\Core\Hook\ComplexOrder $order
   *   Set the order of the implementation.
   */
  public function __construct(
    string $hook,
    string $class,
    string $method,
    Order|ComplexOrder $order,
  ) {
    parent::__construct(... compact('hook', 'method', 'class', 'order'));
  }

}
